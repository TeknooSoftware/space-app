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
namespace Teknoo\Space\Tests\Unit\Object\DTO;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\DTO\WalletCursor;
use Teknoo\Space\Object\Persisted\AccountEnvironment;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(WalletCursor::class)]
class WalletCursorTest extends TestCase
{
    public function testWalksEveryEnvironmentOfTheWalletOnce(): void
    {
        $env1 = $this->createStub(AccountEnvironment::class);
        $env2 = $this->createStub(AccountEnvironment::class);

        $cursor = new WalletCursor(new AccountWallet([$env1, $env2]));

        $this->assertTrue($cursor->valid());
        $this->assertSame($env1, $cursor->current());
        $this->assertSame($cursor, $cursor->next());
        $this->assertTrue($cursor->valid());
        $this->assertSame($env2, $cursor->current());
        $cursor->next();
        $this->assertFalse($cursor->valid());
    }

    public function testIsNotValidOnAnEmptyWallet(): void
    {
        $cursor = new WalletCursor(new AccountWallet([]));

        $this->assertFalse($cursor->valid());
    }
}
