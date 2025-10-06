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

namespace Teknoo\Space\Tests\Unit\Object\Config;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Kubernetes\Client;
use Teknoo\Space\Object\Config\Cluster as ClusterConfig;

/**
 * Class ClusterTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ClusterConfig::class)]
class ClusterTest extends TestCase
{
    private ClusterConfig $cluster;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->cluster = new ClusterConfig(
            name: 'foo',
            sluggyName: 'bar',
            type: 'foo',
            masterAddress: 'foo',
            storageProvisioner: 'foo',
            dashboardAddress: 'foo',
            kubernetesClient: $this->createMock(Client::class),
            token: 'foo',
            supportRegistry: true,
            useHnc: true,
            isExternal: false,
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            Client::class,
            $this->cluster->getKubernetesClient(),
        );
    }

    public function testConstructWithCallable(): void
    {
        $client = $this->createMock(Client::class);
        $callable = fn () => $client;

        $cluster = new ClusterConfig(
            name: 'foo',
            sluggyName: 'bar',
            type: 'foo',
            masterAddress: 'foo',
            storageProvisioner: 'foo',
            dashboardAddress: 'foo',
            kubernetesClient: $callable,
            token: 'foo',
            supportRegistry: true,
            useHnc: true,
            isExternal: false,
        );

        $this->assertInstanceOf(Client::class, $cluster->getKubernetesClient());
        $this->assertSame($client, $cluster->getKubernetesClient());
    }

    public function testConstructWithCallableAndNoRegistrySupport(): void
    {
        $client = $this->createMock(Client::class);
        $callable = fn () => $client;

        $cluster = new ClusterConfig(
            name: 'foo',
            sluggyName: 'bar',
            type: 'foo',
            masterAddress: 'foo',
            storageProvisioner: 'foo',
            dashboardAddress: 'foo',
            kubernetesClient: $callable,
            token: 'foo',
            supportRegistry: false,
            useHnc: true,
            isExternal: false,
        );

        $this->assertInstanceOf(Client::class, $cluster->getKubernetesClient());
    }

    public function testGetKubernetesClientThrowsExceptionWhenNotInitialized(): void
    {
        $cluster = new ClusterConfig(
            name: 'foo',
            sluggyName: 'bar',
            type: 'foo',
            masterAddress: 'foo',
            storageProvisioner: 'foo',
            dashboardAddress: 'foo',
            kubernetesClient: fn () => null,
            token: 'foo',
            supportRegistry: false,
            useHnc: true,
            isExternal: false,
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Error during kubernetes client's initializing");
        $cluster->getKubernetesClient();
    }

    public function testGetKubernetesRegistryClient(): void
    {
        $this->assertInstanceOf(
            Client::class,
            $this->cluster->getKubernetesRegistryClient(),
        );
    }

    public function testGetKubernetesRegistryClientThrowsExceptionWhenNotSupported(): void
    {
        $cluster = new ClusterConfig(
            name: 'foo',
            sluggyName: 'bar',
            type: 'foo',
            masterAddress: 'foo',
            storageProvisioner: 'foo',
            dashboardAddress: 'foo',
            kubernetesClient: $this->createMock(Client::class),
            token: 'foo',
            supportRegistry: false,
            useHnc: true,
            isExternal: false,
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error this cluster does not support OCI registry');
        $cluster->getKubernetesRegistryClient();
    }

    public function testGetKubernetesRegistryClientWithCallableInitialization(): void
    {
        $client = $this->createMock(Client::class);
        $callable = fn () => $client;

        $cluster = new ClusterConfig(
            name: 'foo',
            sluggyName: 'bar',
            type: 'foo',
            masterAddress: 'foo',
            storageProvisioner: 'foo',
            dashboardAddress: 'foo',
            kubernetesClient: $callable,
            token: 'foo',
            supportRegistry: true,
            useHnc: true,
            isExternal: false,
        );

        $registryClient = $cluster->getKubernetesRegistryClient();
        $this->assertInstanceOf(Client::class, $registryClient);
    }
}
