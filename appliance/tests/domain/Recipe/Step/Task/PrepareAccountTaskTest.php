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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Task;

use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Contracts\DTO\NewTaskInterface;
use Teknoo\Space\Object\DTO\Task\RefreshQuotaTask;
use Teknoo\Space\Object\DTO\Task\ReinstallEnvironmentTask;
use Teknoo\Space\Recipe\Step\Task\PrepareAccountTask;

/**
 * Class PrepareAccountTaskTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(PrepareAccountTask::class)]
class PrepareAccountTaskTest extends TestCase
{
    private PrepareAccountTask $prepareAccountTask;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareAccountTask = new PrepareAccountTask();
    }

    private function buildAccount(): Account
    {
        $account = $this->createStub(Account::class);
        $account->method('getId')->willReturn('account-id');

        return $account;
    }

    public function testInvokeBuildsAnEnvironmentTask(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(
                    static function (array $workplan): bool {
                        $task = $workplan[NewTaskInterface::class] ?? null;

                        return $task instanceof ReinstallEnvironmentTask
                            && 'account-id' === $task->accountId
                            && 'prod' === $task->envName
                            && 'cluster' === $task->clusterName
                            && !empty($task->taskId);
                    }
                )
            )
            ->willReturnSelf();

        $this->assertInstanceOf(
            PrepareAccountTask::class,
            ($this->prepareAccountTask)(
                manager: $manager,
                accountInstance: $this->buildAccount(),
                taskClass: ReinstallEnvironmentTask::class,
                envName: 'prod',
                clusterName: 'cluster',
            ),
        );
    }

    public function testInvokeBuildsAnAccountLevelTaskWithoutEnvironment(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(
                    static function (array $workplan): bool {
                        $task = $workplan[NewTaskInterface::class] ?? null;

                        return $task instanceof RefreshQuotaTask
                            && 'account-id' === $task->accountId
                            && null === $task->envName
                            && null === $task->clusterName;
                    }
                )
            )
            ->willReturnSelf();

        $this->assertInstanceOf(
            PrepareAccountTask::class,
            ($this->prepareAccountTask)(
                manager: $manager,
                accountInstance: $this->buildAccount(),
                taskClass: RefreshQuotaTask::class,
            ),
        );
    }

    public function testInvokeRefusesAClassWhichIsNotAnAccountTask(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('teknoo.space.error.task.invalid_class');

        ($this->prepareAccountTask)(
            manager: $manager,
            accountInstance: $this->buildAccount(),
            taskClass: stdClass::class,
        );
    }
}
