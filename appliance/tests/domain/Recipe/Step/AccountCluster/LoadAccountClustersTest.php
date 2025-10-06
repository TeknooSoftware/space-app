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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\AccountCluster;

use Kubernetes\Client;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Infrastructures\Kubernetes\Contracts\ClientFactoryInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Kubernetes\RepositoryRegistry;
use Teknoo\Space\Loader\AccountClusterLoader;
use Teknoo\Space\Object\Config\Cluster;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Persisted\AccountCluster;
use Teknoo\Space\Recipe\Step\AccountCluster\LoadAccountClusters;

/**
 * Class loadAccountClustersTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(LoadAccountClusters::class)]
class LoadAccountClustersTest extends TestCase
{
    private LoadAccountClusters $loadAccountClusters;

    private AccountClusterLoader&MockObject $loader;

    private readonly ClientFactoryInterface&MockObject $clientFactory;

    private readonly RepositoryRegistry&MockObject $repositoryRegistry;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = $this->createMock(AccountClusterLoader::class);
        $this->clientFactory = $this->createMock(ClientFactoryInterface::class);
        $this->repositoryRegistry = $this->createMock(RepositoryRegistry::class);
        $this->loadAccountClusters = new LoadAccountClusters(
            loader: $this->loader,
            clientFactory: $this->clientFactory,
            repositoryRegistry: $this->repositoryRegistry,
        );
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            LoadAccountClusters::class,
            ($this->loadAccountClusters)(
                $this->createMock(ManagerInterface::class),
                $this->createMock(ClusterCatalog::class),
                $this->createMock(Account::class),
            ),
        );
    }

    public function testInvokeWithNullAccount(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->loader->expects($this->never())->method('query');

        $this->assertInstanceOf(
            LoadAccountClusters::class,
            ($this->loadAccountClusters)(
                manager: $manager,
                clusterCatalog: $this->createMock(ClusterCatalog::class),
                accountInstance: null,
            ),
        );
    }

    public function testInvokeWithClusterCatalogHavingParent(): void
    {
        $clusterCatalog = $this->createMock(ClusterCatalog::class);
        $clusterCatalog->expects($this->once())
            ->method('hasParentCatalog')
            ->willReturn(true);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->loader->expects($this->never())->method('query');

        $this->assertInstanceOf(
            LoadAccountClusters::class,
            ($this->loadAccountClusters)(
                manager: $manager,
                clusterCatalog: $clusterCatalog,
                accountInstance: $this->createMock(Account::class),
            ),
        );
    }

    public function testInvokeWithClusters(): void
    {
        $accountCluster = $this->createMock(AccountCluster::class);
        $configCluster = new Cluster(
            name: 'cluster-name',
            sluggyName: 'cluster-slug',
            type: 'kubernetes',
            masterAddress: 'https://master',
            storageProvisioner: 'provisioner',
            dashboardAddress: 'https://dashboard',
            kubernetesClient: fn () => $this->createMock(Client::class),
            token: 'token',
            supportRegistry: true,
            useHnc: false,
            isExternal: false,
        );

        $accountCluster->expects($this->once())
            ->method('convertToConfigCluster')
            ->with($this->clientFactory, $this->repositoryRegistry)
            ->willReturn($configCluster);

        $clusterCatalog = $this->createMock(ClusterCatalog::class);
        $clusterCatalog->expects($this->once())
            ->method('hasParentCatalog')
            ->willReturn(false);

        $this->loader->expects($this->once())
            ->method('query')
            ->willReturnCallback(function ($query, $promise) use ($accountCluster) {
                $promise->success([$accountCluster]);
                return $this->loader;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan[ClusterCatalog::class])
                        && $workplan[ClusterCatalog::class] instanceof ClusterCatalog
                        && isset($workplan['clusterCatalog'])
                        && $workplan['clusterCatalog'] instanceof ClusterCatalog;
                })
            );

        $this->assertInstanceOf(
            LoadAccountClusters::class,
            ($this->loadAccountClusters)(
                manager: $manager,
                clusterCatalog: $clusterCatalog,
                accountInstance: $this->createMock(Account::class),
            ),
        );
    }

    public function testInvokeWithEmptyClusters(): void
    {
        $clusterCatalog = $this->createMock(ClusterCatalog::class);
        $clusterCatalog->expects($this->once())
            ->method('hasParentCatalog')
            ->willReturn(false);

        $this->loader->expects($this->once())
            ->method('query')
            ->willReturnCallback(function ($query, $promise) {
                $promise->success([]);
                return $this->loader;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) use ($clusterCatalog) {
                    return isset($workplan[ClusterCatalog::class])
                        && $workplan[ClusterCatalog::class] === $clusterCatalog;
                })
            );

        $this->assertInstanceOf(
            LoadAccountClusters::class,
            ($this->loadAccountClusters)(
                manager: $manager,
                clusterCatalog: $clusterCatalog,
                accountInstance: $this->createMock(Account::class),
            ),
        );
    }

    public function testInvokeWithError(): void
    {
        $clusterCatalog = $this->createMock(ClusterCatalog::class);
        $clusterCatalog->expects($this->once())
            ->method('hasParentCatalog')
            ->willReturn(false);

        $this->loader->expects($this->once())
            ->method('query')
            ->willReturnCallback(function ($query, $promise) {
                $promise->fail(new \Exception('Test error', 500));
                return $this->loader;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($exception) {
                    return $exception instanceof \DomainException
                        && 'teknoo.space.error.space_account.account_environment.fetching' === $exception->getMessage()
                        && 500 === $exception->getCode();
                })
            );

        $this->assertInstanceOf(
            LoadAccountClusters::class,
            ($this->loadAccountClusters)(
                manager: $manager,
                clusterCatalog: $clusterCatalog,
                accountInstance: $this->createMock(Account::class),
            ),
        );
    }

    public function testInvokeWithErrorCodeZero(): void
    {
        $clusterCatalog = $this->createMock(ClusterCatalog::class);
        $clusterCatalog->expects($this->once())
            ->method('hasParentCatalog')
            ->willReturn(false);

        $this->loader->expects($this->once())
            ->method('query')
            ->willReturnCallback(function ($query, $promise) {
                $promise->fail(new \Exception('Test error', 0));
                return $this->loader;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($exception) {
                    return $exception instanceof \DomainException
                        && $exception->getCode() === 404;
                })
            );

        $this->assertInstanceOf(
            LoadAccountClusters::class,
            ($this->loadAccountClusters)(
                manager: $manager,
                clusterCatalog: $clusterCatalog,
                accountInstance: $this->createMock(Account::class),
            ),
        );
    }
}
