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

use DomainException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Teknoo\East\Paas\Infrastructures\Kubernetes\Contracts\ClientFactoryInterface;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\DockerComposeCluster;
use Teknoo\Space\Object\Config\KubernetesCluster;

use function base64_encode;

/**
 * Exercises the `teknoo.space.clusters_catalog` builder closure in
 * `appliance/config/di.variables.clusters.php` — specifically the type-aware branch (step-11). `config/` is
 * outside the coverage scope, so this test documents/guards the branch behaviour rather than adding coverage.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversNothing]
class ClustersCatalogBuilderTest extends TestCase
{
    /**
     * @param array<int, array<string, mixed>> $definitions
     */
    private function buildCatalog(array $definitions): ClusterCatalog
    {
        // `require` (not `_once`) re-executes the file, yielding a fresh builder closure with its own
        // `static $clusterCatalog`, so each test invocation is isolated.
        /** @var array<string, callable> $config */
        $config = require __DIR__ . '/../../config/di.variables.clusters.php';
        $builder = $config['teknoo.space.clusters_catalog'];

        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturnCallback(
                static fn (string $id): bool => 'teknoo.space.clusters_catalog.definitions' === $id
            );
        $container->method('get')
            ->willReturnCallback(
                fn (string $id): mixed => match ($id) {
                    'teknoo.space.clusters_catalog.definitions' => $definitions,
                    'teknoo.space.clusters.default_cluster.master' => '',
                    'teknoo.space.clusters.default_cluster.name' => '',
                    'teknoo.east.paas.default_storage_provider' => 'space-nfs',
                    ClientFactoryInterface::class => $this->createStub(ClientFactoryInterface::class),
                    default => null,
                }
            );

        return $builder($container);
    }

    /**
     * @return array<string, mixed>
     */
    private function kubernetesDefinition(): array
    {
        return [
            'name' => 'K8s One',
            'type' => 'kubernetes',
            'master' => 'https://k8s.example.com',
            'dashboard' => 'https://dashboard.example.com',
            'create_account' => [
                'token' => 'a-token',
                'ca_cert' => base64_encode('a-ca-cert'),
            ],
            'support_registry' => true,
            'use_hnc' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dockerComposeDefinition(): array
    {
        return [
            'name' => 'DC One',
            'type' => 'docker-compose',
            'master' => 'ssh://deployer@docker-host.example.com:22',
            'support_registry' => false,
            'ssh' => [
                'client_key' => '-----BEGIN OPENSSH PRIVATE KEY-----KEY',
                'known_hosts' => 'host ssh-ed25519 AAAA',
            ],
        ];
    }

    public function testKubernetesEntryBuildsKubernetesCluster(): void
    {
        $catalog = $this->buildCatalog([$this->kubernetesDefinition()]);
        $cluster = $catalog->getCluster('K8s One');

        $this->assertInstanceOf(KubernetesCluster::class, $cluster);
        $this->assertSame('https://k8s.example.com', $cluster->masterAddress);
        $this->assertTrue($cluster->supportRegistry);
    }

    public function testMissingTypeDefaultsToKubernetes(): void
    {
        $definition = $this->kubernetesDefinition();
        unset($definition['type']);

        $cluster = $this->buildCatalog([$definition])->getCluster('K8s One');

        $this->assertInstanceOf(KubernetesCluster::class, $cluster);
    }

    public function testDockerComposeEntryBuildsDockerComposeCluster(): void
    {
        $cluster = $this->buildCatalog([$this->dockerComposeDefinition()])->getCluster('DC One');

        $this->assertInstanceOf(DockerComposeCluster::class, $cluster);
        $this->assertNotInstanceOf(KubernetesCluster::class, $cluster);
        $this->assertSame('ssh://deployer@docker-host.example.com:22', $cluster->masterAddress);
        $this->assertSame('-----BEGIN OPENSSH PRIVATE KEY-----KEY', $cluster->clientKey);
        $this->assertSame('host ssh-ed25519 AAAA', $cluster->caCertificate);
        $this->assertFalse($cluster->supportRegistry);
        $this->assertFalse($cluster->useHnc);
    }

    public function testMixedCatalogBuildsBothTypes(): void
    {
        $catalog = $this->buildCatalog([$this->kubernetesDefinition(), $this->dockerComposeDefinition()]);

        $this->assertInstanceOf(KubernetesCluster::class, $catalog->getCluster('K8s One'));
        $this->assertInstanceOf(DockerComposeCluster::class, $catalog->getCluster('DC One'));
    }

    public function testMalformedDockerComposeEntryWithoutClientKeyThrows(): void
    {
        $definition = $this->dockerComposeDefinition();
        unset($definition['ssh']['client_key']);

        $this->expectException(DomainException::class);

        $this->buildCatalog([$definition]);
    }
}
