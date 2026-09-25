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
namespace Teknoo\Space\Tests\Unit\Recipe\Step\AccountEnvironment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\DTO\WalletCursor;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Recipe\Step\AccountEnvironment\EndLoopingOnWallet;
use Teknoo\Space\Recipe\Step\AccountEnvironment\StartLoopingOnWallet;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(StartLoopingOnWallet::class)]
class StartLoopingOnWalletTest extends TestCase
{
    public function testOpensTheLoopOnTheFirstEnvironment(): void
    {
        $env1 = $this->createStub(AccountEnvironment::class);
        $wallet = new AccountWallet([$env1, $this->createStub(AccountEnvironment::class)]);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('continue');
        $updates = [];
        $manager->expects($this->exactly(2))
            ->method('updateWorkPlan')
            ->willReturnCallback(function (array $workPlan) use (&$updates, $manager): ManagerInterface {
                $updates[] = $workPlan;

                return $manager;
            });

        $step = new StartLoopingOnWallet();

        $this->assertInstanceOf(StartLoopingOnWallet::class, $step($manager, $wallet));

        $this->assertInstanceOf(WalletCursor::class, $updates[0][WalletCursor::class]);
        $this->assertSame($env1, $updates[1][AccountEnvironment::class]);
    }

    public function testResumesTheLoopFromTheCursorInTheWorkPlan(): void
    {
        $env1 = $this->createStub(AccountEnvironment::class);
        $env2 = $this->createStub(AccountEnvironment::class);
        $wallet = new AccountWallet([$env1, $env2]);
        $cursor = new WalletCursor($wallet)->next();

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('continue');
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with([AccountEnvironment::class => $env2])
            ->willReturnSelf();

        $step = new StartLoopingOnWallet();

        $this->assertInstanceOf(StartLoopingOnWallet::class, $step($manager, $wallet, $cursor));
    }

    public function testJumpsToTheEndOnAnEmptyWallet(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with($this->callback(
                static fn (array $workPlan): bool => $workPlan[WalletCursor::class] instanceof WalletCursor
            ))
            ->willReturnSelf();
        $manager->expects($this->once())
            ->method('continue')
            ->with([], EndLoopingOnWallet::class)
            ->willReturnSelf();

        $step = new StartLoopingOnWallet();

        $this->assertInstanceOf(StartLoopingOnWallet::class, $step($manager, new AccountWallet([])));
    }

    public function testJumpsToTheEndWhenTheCursorIsExhausted(): void
    {
        $wallet = new AccountWallet([$this->createStub(AccountEnvironment::class)]);
        $cursor = new WalletCursor($wallet)->next();

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');
        $manager->expects($this->once())
            ->method('continue')
            ->with([], EndLoopingOnWallet::class)
            ->willReturnSelf();

        $step = new StartLoopingOnWallet();

        $this->assertInstanceOf(StartLoopingOnWallet::class, $step($manager, $wallet, $cursor));
    }

    public function testTheSameStepInstanceRestartsFromTheFirstEnvironmentOnEachExecution(): void
    {
        //A worker consumes several tasks with the same plan (and step) instance: the loop state must not leak
        //from one execution to the next, unlike East Common's StartLoopingOn.
        $env1 = $this->createStub(AccountEnvironment::class);
        $step = new StartLoopingOnWallet();

        foreach ([1, 2] as $execution) {
            $wallet = new AccountWallet([$env1]);
            $updates = [];
            $manager = $this->createMock(ManagerInterface::class);
            $manager->expects($this->never())->method('continue');
            $manager->expects($this->exactly(2))
                ->method('updateWorkPlan')
                ->willReturnCallback(function (array $workPlan) use (&$updates, $manager): ManagerInterface {
                    $updates[] = $workPlan;

                    return $manager;
                });

            $step($manager, $wallet);

            $this->assertSame($env1, $updates[1][AccountEnvironment::class], "Execution #$execution");
        }
    }
}
