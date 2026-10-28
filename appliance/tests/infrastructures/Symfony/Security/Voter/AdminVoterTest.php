<?php

/*
 * Teknoo Space.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/applications/space Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Security\Voter;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\Common\Object\Media;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Job as JobODM;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\Job;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Infrastructures\Symfony\Security\Voter\AdminVoter;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\DTO\SpaceUser;
use Teknoo\Space\Object\Persisted\AccountCluster;
use Teknoo\Space\Object\Persisted\AccountData;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;
use Teknoo\Space\Object\Persisted\AccountRegistry;
use Teknoo\Space\Object\Persisted\ProjectPersistedVariable;

/**
 * Class AdminVoterTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AdminVoter::class)]
class AdminVoterTest extends TestCase
{
    private AdminVoter $adminVoter;

    private string $adminRoleName;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRoleName = '42';
        $this->adminVoter = new AdminVoter($this->adminRoleName);
    }

    public function testVote(): void
    {
        $this->assertIsInt(
            $this->adminVoter->vote(
                $this->createStub(TokenInterface::class),
                'foo',
                ['foo' => 'bar'],
            ),
        );
    }

    /**
     * @param string[] $roles
     */
    private function createToken(array $roles): TokenInterface
    {
        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($this->createStub(AbstractUser::class));
        $token->method('getRoleNames')->willReturn($roles);

        return $token;
    }

    /**
     * @return iterable<string, array{class-string, string}>
     */
    public static function grantedProvider(): iterable
    {
        foreach ([Account::class, SpaceAccount::class, User::class, SpaceUser::class, Job::class] as $class) {
            yield "$class access" => [$class, 'access'];
            yield "$class list" => [$class, 'list'];
        }

        yield 'Job proxy access' => [JobODM::class, 'access'];
        yield Project::class . ' list' => [Project::class, 'list'];
        yield SpaceProject::class . ' list' => [SpaceProject::class, 'list'];
        yield AccountCluster::class . ' list' => [AccountCluster::class, 'list'];
    }

    /**
     * @param class-string $class
     */
    #[DataProvider('grantedProvider')]
    public function testVoteGrantedOnTheObjectsAnAdministratorManages(string $class, string $attribute): void
    {
        $vote = new Vote();

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->adminVoter->vote(
                $this->createToken(['ROLE_USER', $this->adminRoleName]),
                $this->createStub($class),
                [$attribute],
                $vote,
            ),
        );
        $this->assertSame(['teknoo.space.vote.granted.user_is_admin'], $vote->reasons);
    }

    /**
     * @return iterable<string, array{class-string, string}>
     */
    public static function abstainProvider(): iterable
    {
        yield Project::class . ' access' => [Project::class, 'access'];
        yield SpaceProject::class . ' access' => [SpaceProject::class, 'access'];
        yield AccountCluster::class . ' access' => [AccountCluster::class, 'access'];

        foreach (
            [
                AccountData::class,
                AccountEnvironment::class,
                AccountHistory::class,
                AccountPersistedVariable::class,
                AccountRegistry::class,
                ProjectPersistedVariable::class,
                Media::class,
                stdClass::class,
                Request::class,
            ] as $class
        ) {
            yield "$class access" => [$class, 'access'];
            yield "$class list" => [$class, 'list'];
        }

        yield Account::class . ' role' => [Account::class, 'ROLE_ADMIN'];
        yield Account::class . ' authenticated' => [Account::class, 'IS_AUTHENTICATED_FULLY'];
        yield Account::class . ' unknown' => [Account::class, 'delete'];
    }

    /**
     * @param class-string $class
     */
    #[DataProvider('abstainProvider')]
    public function testVoteAbstainOnTheOtherObjectsAndAttributes(string $class, string $attribute): void
    {
        $vote = new Vote();

        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->adminVoter->vote(
                $this->createToken(['ROLE_USER', $this->adminRoleName]),
                $this->createStub($class),
                [$attribute],
                $vote,
            ),
        );
        $this->assertSame([], $vote->reasons);
    }

    public function testVoteAbstainOnAnExpression(): void
    {
        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->adminVoter->vote(
                $this->createToken(['ROLE_USER', $this->adminRoleName]),
                $this->createStub(Account::class),
                [new Expression('true')],
            ),
        );
    }

    public function testVoteAbstainWithoutSubject(): void
    {
        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->adminVoter->vote(
                $this->createToken(['ROLE_USER', $this->adminRoleName]),
                null,
                ['access'],
            ),
        );
    }

    public function testVoteAbstainWithoutAUser(): void
    {
        $token = $this->createStub(TokenInterface::class);
        $token->method('getRoleNames')->willReturn([$this->adminRoleName]);

        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->adminVoter->vote(
                $token,
                $this->createStub(Account::class),
                ['access'],
            ),
        );
    }

    public function testVoteAbstainWhenUserHasNotAdminRole(): void
    {
        $vote = new Vote();

        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->adminVoter->vote(
                $this->createToken(['ROLE_USER']),
                $this->createStub(Account::class),
                ['access'],
                $vote,
            ),
        );
        $this->assertSame([], $vote->reasons);
    }
}
