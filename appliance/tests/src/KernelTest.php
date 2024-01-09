<?php

/*
 * Teknoo Space.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\App;

use Teknoo\Space\App\Kernel;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Teknoo\Space\App\Kernel
 */
class KernelTest extends TestCase
{
    public function buildKernel(): Kernel
    {
        return new Kernel('test', false);
    }

    public function testGetCacheDir()
    {
        self::assertIsString($this->buildKernel()->getCacheDir());
    }

    public function testGetLogDir()
    {
        self::assertIsString($this->buildKernel()->getLogDir());
    }

    public function testTegisterBundles()
    {
        foreach ($this->buildKernel()->registerBundles() as $bundle) {
            self::assertIsObject($bundle);
        }
    }
}
