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

namespace Teknoo\Space\Tests\Unit\Config;

use ArrayObject;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Teknoo\Space\Object\Config\DashboardProfile;
use Teknoo\Space\Object\Config\DashboardProfileCatalog;

/**
 * Exercises the built-in profiles of Kubernetes web dashboards and the `DashboardProfileCatalog` builder defined in
 * `appliance/config/di.variables.clusters.php`.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversNothing]
class DashboardProfilesTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function loadConfig(): array
    {
        /** @var array<string, mixed> $config */
        $config = require __DIR__ . '/../../config/di.variables.clusters.php';

        return $config;
    }

    /**
     * @return array<string, DashboardProfile>
     */
    private function builtInProfiles(): array
    {
        $profiles = $this->loadConfig()['teknoo.space.dashboard.profiles']();
        $this->assertInstanceOf(ArrayObject::class, $profiles);

        return $profiles->getArrayCopy();
    }

    private function buildCatalog(mixed $profiles = null): DashboardProfileCatalog
    {
        $config = $this->loadConfig();
        $profiles ??= $config['teknoo.space.dashboard.profiles']();

        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnCallback(
                static fn (string $id): mixed => match ($id) {
                    'teknoo.space.dashboard.profiles' => $profiles,
                    default => null,
                }
            );

        return $config[DashboardProfileCatalog::class]($container);
    }

    /**
     * @return iterable<string, array{?string, bool}>
     */
    public static function externalDashboardsSwitchProvider(): iterable
    {
        yield 'not defined' => [null, false];
        yield 'empty' => ['', false];
        yield 'false' => ['false', false];
        yield 'zero' => ['0', false];
        yield 'true' => ['true', true];
        yield 'one' => ['1', true];
        yield 'yes' => ['yes', true];
    }

    #[DataProvider('externalDashboardsSwitchProvider')]
    public function testTheExternalDashboardsSwitch(?string $value, bool $expected): void
    {
        $previous = $_ENV['SPACE_DASHBOARD_EXTERNAL_ENABLED'] ?? null;
        unset($_ENV['SPACE_DASHBOARD_EXTERNAL_ENABLED']);
        if (null !== $value) {
            $_ENV['SPACE_DASHBOARD_EXTERNAL_ENABLED'] = $value;
        }

        try {
            $this->assertSame($expected, $this->loadConfig()['teknoo.space.dashboard.external.enabled']);
        } finally {
            unset($_ENV['SPACE_DASHBOARD_EXTERNAL_ENABLED']);
            if (null !== $previous) {
                $_ENV['SPACE_DASHBOARD_EXTERNAL_ENABLED'] = $previous;
            }
        }
    }

    public function testTheBuiltInProfiles(): void
    {
        $catalog = $this->buildCatalog();

        $this->assertSame('headlamp', $catalog->getProfile('headlamp')->name);
        $this->assertSame('kubernetes-dashboard', $catalog->getProfile('kubernetes-dashboard')->name);
    }

    public function testProfilesCanBeAnArray(): void
    {
        $catalog = $this->buildCatalog($this->builtInProfiles());

        $this->assertSame('kubernetes-dashboard', $catalog->getProfile('kubernetes-dashboard')->name);
    }

    public function testHeadlampProfile(): void
    {
        $profile = $this->builtInProfiles()['headlamp'];

        $this->assertTrue($profile->rewriteBasePath);
        $this->assertSame(['Authorization' => 'Bearer a-token'], $profile->renderRequestHeaders('a-token'));
        $this->assertSame('c/main', $profile->renderEntryPath(null));
        $this->assertSame('c/main/workloads?namespace=space-ns', $profile->renderEntryPath('space-ns'));
        $this->assertSame('foo', $profile->resolvePath('foo'));

        $namespaced = $profile->renderHeadSnippet('https://space.test/frame/', 'space-ns');
        $this->assertStringContainsString('c.allowedNamespaces=[n];c.defaultNamespace=n;', $namespaced);
        $this->assertStringContainsString("(window.localStorage,'space-ns')", $namespaced);

        $allNamespaces = $profile->renderHeadSnippet('https://space.test/frame/', null);
        $this->assertStringContainsString('delete c.allowedNamespaces;delete c.defaultNamespace;', $allNamespaces);
        $this->assertStringContainsString("s.removeItem('headlamp-selected-namespace_main')", $allNamespaces);
    }

    public function testLegacyKubernetesDashboardProfile(): void
    {
        $profile = $this->builtInProfiles()['kubernetes-dashboard'];

        $this->assertFalse($profile->rewriteBasePath);
        $this->assertSame(['Authorization' => 'Bearer a-token'], $profile->renderRequestHeaders('a-token'));
        $this->assertSame('#/workloads?namespace=_all', $profile->renderEntryPath(null));
        $this->assertSame('#/workloads?namespace=space-ns', $profile->renderEntryPath('space-ns'));
        $this->assertSame(
            '<base href="https://space.test/frame/">',
            $profile->renderHeadSnippet('https://space.test/frame/', 'space-ns'),
        );
        $this->assertSame(
            '<base href="https://space.test/frame/">',
            $profile->renderHeadSnippet('https://space.test/frame/', null),
        );
        $this->assertSame('assets/config/config.json', $profile->resolvePath('config/config.json'));
    }
}
