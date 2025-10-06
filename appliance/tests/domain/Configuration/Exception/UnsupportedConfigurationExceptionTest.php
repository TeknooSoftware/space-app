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

namespace Teknoo\Space\Tests\Unit\Configuration\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Teknoo\Space\Configuration\Exception\UnsupportedConfigurationException;

/**
 * Class UnsupportedConfigurationExceptionTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(UnsupportedConfigurationException::class)]
class UnsupportedConfigurationExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $exception = new UnsupportedConfigurationException('test message', 123);

        $this->assertInstanceOf(UnsupportedConfigurationException::class, $exception);
        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertEquals('test message', $exception->getMessage());
        $this->assertEquals(123, $exception->getCode());
    }

    public function testThrow(): void
    {
        $this->expectException(UnsupportedConfigurationException::class);
        $this->expectExceptionMessage('test exception');

        throw new UnsupportedConfigurationException('test exception');
    }
}
