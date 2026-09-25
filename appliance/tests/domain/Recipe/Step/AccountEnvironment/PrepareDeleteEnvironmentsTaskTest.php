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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\AccountEnvironment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Contracts\DTO\NewTaskInterface;
use Teknoo\Space\Object\DTO\AccountEnvironmentResume;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\DTO\Task\DeleteEnvironmentsTask;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Recipe\Step\AccountEnvironment\DeleteEnvFromResumes;
use Teknoo\Space\Recipe\Step\AccountEnvironment\PrepareDeleteEnvironmentsTask;

/**
 * Class PrepareDeleteEnvironmentsTaskTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(PrepareDeleteEnvironmentsTask::class)]
class PrepareDeleteEnvironmentsTaskTest extends TestCase
{
    private PrepareDeleteEnvironmentsTask $step;

    protected function setUp(): void
    {
        parent::setUp();

        $this->step = new PrepareDeleteEnvironmentsTask();
    }

    private function buildEnvironment(Account $account, string $id, string $envName): AccountEnvironment
    {
        return new AccountEnvironment(
            $account,
            'Cluster',
            $envName,
            "space-client-foo-$envName",
            'sa',
            'role',
            'rb',
            'ca',
            'cert',
            'key',
            'token',
            [],
        )->setId($id);
    }

    private function buildWallet(Account $account): AccountWallet
    {
        return new AccountWallet([
            $this->buildEnvironment($account, 'env-1', 'dev'),
            $this->buildEnvironment($account, 'env-2', 'prod'),
            new AccountEnvironment($account, 'Cluster', 'new', 'ns', 'sa', 'role', 'rb', 'ca', 'cert', 'key', 't', []),
        ]);
    }

    public function testInvokeJumpsWhenTheFormHasNoEnvironmentsSection(): void
    {
        $account = new Account()->setId('account-id');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');
        $manager->expects($this->once())
            ->method('continue')
            ->with([], DeleteEnvFromResumes::class)
            ->willReturnSelf();

        $this->assertInstanceOf(
            PrepareDeleteEnvironmentsTask::class,
            ($this->step)(
                $manager,
                $this->buildWallet($account),
                new SpaceAccount(account: $account, environments: null),
            ),
        );
    }

    public function testInvokeJumpsWhenNoEnvironmentWasRemoved(): void
    {
        $account = new Account()->setId('account-id');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');
        $manager->expects($this->once())
            ->method('continue')
            ->with([], DeleteEnvFromResumes::class)
            ->willReturnSelf();

        $this->assertInstanceOf(
            PrepareDeleteEnvironmentsTask::class,
            ($this->step)(
                $manager,
                $this->buildWallet($account),
                new SpaceAccount(
                    account: $account,
                    environments: [
                        new AccountEnvironmentResume('Cluster', 'dev', 'env-1'),
                        new AccountEnvironmentResume('Cluster', 'prod', 'env-2'),
                        new AccountEnvironmentResume('Cluster', 'testing', null),
                    ],
                ),
            ),
        );
    }

    public function testInvokeBuildsATaskListingTheRemovedEnvironments(): void
    {
        $account = new Account()->setId('account-id');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('continue');
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(
                    static function (array $workplan): bool {
                        $task = $workplan[NewTaskInterface::class] ?? null;

                        return $task instanceof DeleteEnvironmentsTask
                            && 'account-id' === $task->accountId
                            && null === $task->envName
                            && null === $task->clusterName
                            && !empty($task->taskId)
                            && [
                                [
                                    'envName' => 'prod',
                                    'clusterName' => 'Cluster',
                                    'namespace' => 'space-client-foo-prod',
                                ],
                            ] === $task->environments;
                    }
                )
            )
            ->willReturnSelf();

        $this->assertInstanceOf(
            PrepareDeleteEnvironmentsTask::class,
            ($this->step)(
                $manager,
                $this->buildWallet($account),
                new SpaceAccount(
                    account: $account,
                    environments: [
                        new AccountEnvironmentResume('Cluster', 'dev', 'env-1'),
                    ],
                ),
            ),
        );
    }
}
