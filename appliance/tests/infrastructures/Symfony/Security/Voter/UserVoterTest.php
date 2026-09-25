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
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\Space\Infrastructures\Symfony\Security\Voter\UserVoter;
use Teknoo\Space\Object\DTO\SpaceUser;

/**
 * Class UserVoterTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(UserVoter::class)]
class UserVoterTest extends TestCase
{
    private UserVoter $userVoter;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userVoter = new UserVoter();
    }

    public function testVote(): void
    {
        $this->assertIsInt(
            $this->userVoter->vote(
                $this->createStub(TokenInterface::class),
                'foo',
                ['foo' => 'bar'],
            )
        );
    }

    private function createWrappedUser(User $user): AbstractUser
    {
        $wrappedUser = $this->createStub(AbstractUser::class);
        $wrappedUser->method('getWrappedUser')->willReturn($user);

        return $wrappedUser;
    }

    private function createToken(?User $user): TokenInterface
    {
        $token = $this->createStub(TokenInterface::class);
        if (null === $user) {
            $token->method('getUser')->willReturn(null);

            return $token;
        }

        $token->method('getUser')->willReturn($this->createWrappedUser($user));

        return $token;
    }

    public function testVoteDeniedWhenAnonymous(): void
    {
        $vote = new Vote();

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->userVoter->vote(
                $this->createToken(null),
                new User(),
                ['foo'],
                $vote,
            ),
        );
        $this->assertSame(['teknoo.space.vote.denied.user_anonymous'], $vote->reasons);
    }

    public function testVoteGrantedWithAbstractUserSubject(): void
    {
        $user = (new User())->setId('user-1');
        $vote = new Vote();

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->userVoter->vote(
                $this->createToken($user),
                $this->createWrappedUser((new User())->setId('user-1')),
                ['foo'],
                $vote,
            ),
        );
        $this->assertSame(['teknoo.space.vote.granted.is_require_user'], $vote->reasons);
    }

    public function testVoteGrantedWithSpaceUserSubject(): void
    {
        $user = (new User())->setId('user-1');
        $vote = new Vote();

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->userVoter->vote(
                $this->createToken($user),
                new SpaceUser(user: $user),
                ['foo'],
                $vote,
            ),
        );
        $this->assertSame(['teknoo.space.vote.granted.is_require_user'], $vote->reasons);
    }

    public function testVoteDeniedWhenNotSameUser(): void
    {
        $user = (new User())->setId('user-1');
        $vote = new Vote();

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->userVoter->vote(
                $this->createToken($user),
                (new User())->setId('user-2'),
                ['foo'],
                $vote,
            ),
        );
        $this->assertSame(['teknoo.space.vote.denied.is_not_require_user'], $vote->reasons);
    }
}
