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

namespace Teknoo\Space\Tests\Unit\App;

use PHPUnit\Framework\Attributes\CoversClass;
use Teknoo\Space\App\Kernel;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Symfony\Component\Routing\RouteCollection;
use Teknoo\East\Foundation\Extension\Manager;

#[CoversClass(Kernel::class)]
class KernelTest extends TestCase
{
    public function buildKernel(): Kernel
    {
        return new Kernel('test', false);
    }

    public function testGetCacheDir(): void
    {
        $this->assertIsString($this->buildKernel()->getCacheDir());
    }

    public function testGetLogDir(): void
    {
        $this->assertIsString($this->buildKernel()->getLogDir());
    }

    public function testTegisterBundles(): void
    {
        foreach ($this->buildKernel()->registerBundles() as $bundle) {
            $this->assertIsObject($bundle);
        }
    }

    public function testConfigureRoutes(): void
    {
        $oldDisabledValue = $_ENV['TEKNOO_EAST_EXTENSION_DISABLED'] ?? null;
        $_ENV['TEKNOO_EAST_EXTENSION_DISABLED'] = '1';

        try {
            $loader = $this->createMock(PhpFileLoader::class);
            $loader
                ->expects($this->atLeastOnce())
                ->method('import')
                ->willReturn(new RouteCollection());

            $routes = new RoutingConfigurator(
                new RouteCollection(),
                $loader,
                __FILE__,
                __FILE__,
                'test',
            );

            $method = new ReflectionMethod(Kernel::class, 'configureRoutes');
            $method->invoke($this->buildKernel(), $routes);

            $this->assertInstanceOf(RoutingConfigurator::class, $routes);
        } finally {
            if (null === $oldDisabledValue) {
                unset($_ENV['TEKNOO_EAST_EXTENSION_DISABLED']);
            } else {
                $_ENV['TEKNOO_EAST_EXTENSION_DISABLED'] = $oldDisabledValue;
            }

            Manager::reset();
        }
    }
}
