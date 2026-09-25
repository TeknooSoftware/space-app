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
#[CoversClass(EndLoopingOnWallet::class)]
class EndLoopingOnWalletTest extends TestCase
{
    public function testJumpsBackToTheStartWhileAnEnvironmentRemains(): void
    {
        $cursor = new WalletCursor(new AccountWallet([
            $this->createStub(AccountEnvironment::class),
            $this->createStub(AccountEnvironment::class),
        ]));

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('continue')
            ->with([], StartLoopingOnWallet::class)
            ->willReturnSelf();

        $step = new EndLoopingOnWallet();

        $this->assertInstanceOf(EndLoopingOnWallet::class, $step($manager, $cursor));
        $this->assertTrue($cursor->valid());
    }

    public function testFallsThroughWhenTheWalletIsExhausted(): void
    {
        $cursor = new WalletCursor(new AccountWallet([$this->createStub(AccountEnvironment::class)]));

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('continue');

        $step = new EndLoopingOnWallet();

        $this->assertInstanceOf(EndLoopingOnWallet::class, $step($manager, $cursor));
        $this->assertFalse($cursor->valid());
    }
}
