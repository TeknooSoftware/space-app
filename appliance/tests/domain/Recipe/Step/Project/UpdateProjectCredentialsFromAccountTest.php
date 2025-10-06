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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Project;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Paas\Contracts\Object\ImageRegistryInterface;
use Teknoo\East\Paas\Object\Cluster;
use Teknoo\East\Paas\Object\ClusterCredentials;
use Teknoo\East\Paas\Object\Environment;
use Teknoo\East\Paas\Object\ImageRegistry;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Kubernetes\Client;
use Teknoo\Space\Object\Config\Cluster as ClusterConfig;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Object\Persisted\AccountRegistry;
use Teknoo\Space\Recipe\Step\Project\UpdateProjectCredentialsFromAccount;

/**
 * Class UpdateProjectCredentialsFromAccountTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(UpdateProjectCredentialsFromAccount::class)]
class UpdateProjectCredentialsFromAccountTest extends TestCase
{
    private UpdateProjectCredentialsFromAccount $updateProjectCredentialsFromAccount;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->updateProjectCredentialsFromAccount = new UpdateProjectCredentialsFromAccount();
    }

    public function testInvoke(): void
    {
        $wallet = new AccountWallet(
            [$this->createMock(AccountEnvironment::class)]
        );

        $project = $this->createMock(Project::class);
        $project->expects($this->once())
            ->method('visit')
            ->with($this->isArray());

        $this->assertInstanceOf(
            UpdateProjectCredentialsFromAccount::class,
            ($this->updateProjectCredentialsFromAccount)(
                new SpaceProject($project),
                $wallet,
                $this->createMock(AccountRegistry::class),
                $this->createMock(ClusterCatalog::class),
            )
        );
    }

    public function testInvokeWithImageRegistryUpdate(): void
    {
        $accountRegistry = $this->createMock(AccountRegistry::class);
        $accountRegistry->expects($this->any())
            ->method('getRegistryUrl')
            ->willReturn('https://registry.example.com');
        $accountRegistry->expects($this->once())
            ->method('getRegistryAccountName')
            ->willReturn('account-name');
        $accountRegistry->expects($this->once())
            ->method('getRegistryPassword')
            ->willReturn('password');
        $accountRegistry->expects($this->once())
            ->method('getRegistryConfigName')
            ->willReturn('config-name');

        $imageRegistry = $this->createMock(ImageRegistry::class);

        $project = $this->createMock(Project::class);
        $project->expects($this->once())
            ->method('visit')
            ->willReturnCallback(function ($visitors) use ($project, $imageRegistry) {
                if (isset($visitors['imagesRegistry'])) {
                    $visitors['imagesRegistry']($imageRegistry);
                }

                if (isset($visitors['clusters'])) {
                    $visitors['clusters']([]);
                }

                return $project;
            });
        $project->expects($this->once())
            ->method('setImagesRegistry')
            ->with($this->isInstanceOf(ImageRegistry::class));

        $wallet = new AccountWallet([]);

        $this->assertInstanceOf(
            UpdateProjectCredentialsFromAccount::class,
            ($this->updateProjectCredentialsFromAccount)(
                new SpaceProject($project),
                $wallet,
                $accountRegistry,
                $this->createMock(ClusterCatalog::class),
            )
        );
    }

    public function testInvokeWithNonImageRegistry(): void
    {
        $accountRegistry = $this->createMock(AccountRegistry::class);
        $accountRegistry->expects($this->never())
            ->method('getRegistryUrl');

        $nonImageRegistry = $this->createMock(ImageRegistryInterface::class);

        $project = $this->createMock(Project::class);
        $project->expects($this->once())
            ->method('visit')
            ->willReturnCallback(function ($visitors) use ($project, $nonImageRegistry) {
                if (isset($visitors['imagesRegistry'])) {
                    $visitors['imagesRegistry']($nonImageRegistry);
                }

                if (isset($visitors['clusters'])) {
                    $visitors['clusters']([]);
                }

                return $project;
            });
        $project->expects($this->never())
            ->method('setImagesRegistry');

        $wallet = new AccountWallet([]);

        $this->assertInstanceOf(
            UpdateProjectCredentialsFromAccount::class,
            ($this->updateProjectCredentialsFromAccount)(
                new SpaceProject($project),
                $wallet,
                $accountRegistry,
                $this->createMock(ClusterCatalog::class),
            )
        );
    }

    public function testInvokeWithClusterUpdate(): void
    {
        $accountEnv = $this->createMock(AccountEnvironment::class);
        $accountEnv->expects($this->any())
            ->method('getClusterName')
            ->willReturn('cluster1');
        $accountEnv->expects($this->any())
            ->method('getEnvName')
            ->willReturn('prod');
        $accountEnv->expects($this->once())
            ->method('getNamespace')
            ->willReturn('namespace1');
        $accountEnv->expects($this->once())
            ->method('getCaCertificate')
            ->willReturn('ca-cert');
        $accountEnv->expects($this->once())
            ->method('getClientCertificate')
            ->willReturn('client-cert');
        $accountEnv->expects($this->once())
            ->method('getClientKey')
            ->willReturn('client-key');
        $accountEnv->expects($this->once())
            ->method('getToken')
            ->willReturn('token');

        $wallet = new AccountWallet([$accountEnv]);

        $clusterConfig = new ClusterConfig(
            name: 'foo',
            sluggyName: 'bar',
            type: 'kubernetes',
            masterAddress: 'https://cluster.example.com',
            storageProvisioner: 'foo',
            dashboardAddress: 'foo',
            kubernetesClient: $this->createMock(Client::class),
            token: 'foo',
            supportRegistry: false,
            useHnc: true,
            isExternal: false,
        );

        $clusterCatalog = $this->createMock(ClusterCatalog::class);
        $clusterCatalog->expects($this->once())
            ->method('getCluster')
            ->with('cluster1')
            ->willReturn($clusterConfig);

        $environment = $this->createMock(Environment::class);
        $environment->expects($this->any())
            ->method('__toString')
            ->willReturn('prod');

        $cluster = $this->createMock(Cluster::class);
        $cluster->expects($this->any())
            ->method('__toString')
            ->willReturn('cluster1');
        $cluster->expects($this->once())
            ->method('visit')
            ->willReturnCallback(function ($visitor, $callable) use ($cluster, $environment) {
                $callable($environment);

                return $cluster;
            });
        $cluster->expects($this->once())
            ->method('setType')
            ->with('kubernetes');
        $cluster->expects($this->once())
            ->method('useHierarchicalNamespaces')
            ->with(true);
        $cluster->expects($this->once())
            ->method('setAddress')
            ->with('https://cluster.example.com');
        $cluster->expects($this->once())
            ->method('setLocked')
            ->with(true);
        $cluster->expects($this->once())
            ->method('setNamespace')
            ->with('namespace1');
        $cluster->expects($this->once())
            ->method('setIdentity')
            ->with($this->isInstanceOf(ClusterCredentials::class));

        $project = $this->createMock(Project::class);
        $project->expects($this->once())
            ->method('visit')
            ->willReturnCallback(function ($visitors) use ($project, $cluster) {
                if (isset($visitors['imagesRegistry'])) {
                    $visitors['imagesRegistry']($this->createMock(ImageRegistryInterface::class));
                }

                if (isset($visitors['clusters'])) {
                    $visitors['clusters']([$cluster]);
                }

                return $project;
            });

        $this->assertInstanceOf(
            UpdateProjectCredentialsFromAccount::class,
            ($this->updateProjectCredentialsFromAccount)(
                new SpaceProject($project),
                $wallet,
                $this->createMock(AccountRegistry::class),
                $clusterCatalog,
            )
        );
    }

    public function testInvokeWithClusterNotInWallet(): void
    {
        $wallet = new AccountWallet([]);

        $environment = $this->createMock(Environment::class);
        $environment->expects($this->any())
            ->method('__toString')
            ->willReturn('prod');

        $cluster = $this->createMock(Cluster::class);
        $cluster->expects($this->any())
            ->method('__toString')
            ->willReturn('cluster1');
        $cluster->expects($this->once())
            ->method('visit')
            ->willReturnCallback(function ($visitor, $callable) use ($cluster, $environment) {
                $callable($environment);

                return $cluster;
            });
        $cluster->expects($this->never())
            ->method('setType');
        $cluster->expects($this->never())
            ->method('setNamespace');

        $project = $this->createMock(Project::class);
        $project->expects($this->once())
            ->method('visit')
            ->willReturnCallback(function ($visitors) use ($project, $cluster) {
                if (isset($visitors['imagesRegistry'])) {
                    $visitors['imagesRegistry']($this->createMock(ImageRegistryInterface::class));
                }

                if (isset($visitors['clusters'])) {
                    $visitors['clusters']([$cluster]);
                }

                return $project;
            });

        $this->assertInstanceOf(
            UpdateProjectCredentialsFromAccount::class,
            ($this->updateProjectCredentialsFromAccount)(
                new SpaceProject($project),
                $wallet,
                $this->createMock(AccountRegistry::class),
                $this->createMock(ClusterCatalog::class),
            )
        );
    }

    public function testInvokeWithNonClusterObject(): void
    {
        $wallet = new AccountWallet([]);

        $notACluster = new \stdClass();

        $project = $this->createMock(Project::class);
        $project->expects($this->once())
            ->method('visit')
            ->willReturnCallback(function ($visitors) use ($project, $notACluster) {
                if (isset($visitors['imagesRegistry'])) {
                    $visitors['imagesRegistry']($this->createMock(ImageRegistryInterface::class));
                }

                if (isset($visitors['clusters'])) {
                    $visitors['clusters']([$notACluster]);
                }

                return $project;
            });

        $this->assertInstanceOf(
            UpdateProjectCredentialsFromAccount::class,
            ($this->updateProjectCredentialsFromAccount)(
                new SpaceProject($project),
                $wallet,
                $this->createMock(AccountRegistry::class),
                $this->createMock(ClusterCatalog::class),
            )
        );
    }
}
