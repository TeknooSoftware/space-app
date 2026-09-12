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
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\Job;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Infrastructures\Symfony\Security\Voter\JobVoter;

/**
 * Class JobVoterTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(JobVoter::class)]
class JobVoterTest extends TestCase
{
    private JobVoter $jobVoter;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->jobVoter = new JobVoter();
    }

    public function testVote(): void
    {
        $this->assertIsInt(
            $this->jobVoter->vote(
                $this->createStub(TokenInterface::class),
                'foo',
                ['foo' => 'bar'],
            )
        );
    }

    private function createToken(?User $user): TokenInterface
    {
        $token = $this->createStub(TokenInterface::class);
        if (null === $user) {
            $token->method('getUser')->willReturn(null);

            return $token;
        }

        $wrappedUser = $this->createStub(AbstractUser::class);
        $wrappedUser->method('getWrappedUser')->willReturn($user);
        $token->method('getUser')->willReturn($wrappedUser);

        return $token;
    }

    private function createJob(?Account $account): Job
    {
        $job = $this->createStub(Job::class);
        if (null === $account) {
            $job->method('getProject')->willReturn(null);

            return $job;
        }

        $project = $this->createStub(Project::class);
        $project->method('getAccount')->willReturn($account);
        $job->method('getProject')->willReturn($project);

        return $job;
    }

    public function testVoteDeniedWhenAnonymous(): void
    {
        $vote = new Vote();

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->jobVoter->vote(
                $this->createToken(null),
                $this->createJob(null),
                ['foo'],
                $vote,
            ),
        );
        $this->assertSame(['teknoo.space.vote.denied.user_anonymous'], $vote->reasons);
    }

    public function testVoteAbstainWhenJobHasNoProject(): void
    {
        $user = (new User())->setId('user-1');

        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->jobVoter->vote(
                $this->createToken($user),
                $this->createJob(null),
                ['foo'],
                new Vote(),
            ),
        );
    }

    public function testVoteGrantedWhenUserInAccount(): void
    {
        $user = (new User())->setId('user-1');
        $account = (new Account())->setName('foo')->setUsers([$user]);
        $vote = new Vote();

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->jobVoter->vote(
                $this->createToken($user),
                $this->createJob($account),
                ['foo'],
                $vote,
            ),
        );
        $this->assertSame(['teknoo.space.vote.granted.user_in_account'], $vote->reasons);
    }

    public function testVoteAbstainWhenUserNotInAccount(): void
    {
        $user = (new User())->setId('user-1');
        $other = (new User())->setId('user-2');
        $account = (new Account())->setName('foo')->setUsers([$other]);

        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->jobVoter->vote(
                $this->createToken($user),
                $this->createJob($account),
                ['foo'],
            ),
        );
    }
}
