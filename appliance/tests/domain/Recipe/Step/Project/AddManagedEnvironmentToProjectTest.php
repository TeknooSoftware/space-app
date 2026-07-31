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
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Cluster as EastCluster;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Kubernetes\Client;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\DockerComposeCluster;
use Teknoo\Space\Object\Config\KubernetesCluster;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Recipe\Step\Project\AddManagedEnvironmentToProject;

/**
 * Class AddManagedEnvironmentToProjectTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AddManagedEnvironmentToProject::class)]
class AddManagedEnvironmentToProjectTest extends TestCase
{
    private AddManagedEnvironmentToProject $addManagedEnvironmentToProject;


    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->addManagedEnvironmentToProject = new AddManagedEnvironmentToProject();
    }

    public function testInvoke(): void
    {
        $wallet = new AccountWallet(
            [$this->createStub(AccountEnvironment::class)]
        );

        $this->assertInstanceOf(
            AddManagedEnvironmentToProject::class,
            ($this->addManagedEnvironmentToProject)(
                $this->createStub(ManagerInterface::class),
                new SpaceProject($this->createStub(Project::class)),
                $wallet,
                $this->createStub(ClusterCatalog::class),
            )
        );
    }

    public function testInvokeWithOnlyClusterName(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with($this->callback(fn ($e) => $e instanceof \LogicException));

        $wallet = new AccountWallet([]);
        $spaceProject = new SpaceProject($this->createStub(Project::class));
        $spaceProject->addClusterName = 'cluster1';

        $this->assertInstanceOf(
            AddManagedEnvironmentToProject::class,
            ($this->addManagedEnvironmentToProject)(
                $manager,
                $spaceProject,
                $wallet,
                $this->createStub(ClusterCatalog::class),
            )
        );
    }

    public function testInvokeWithOnlyClusterEnv(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with($this->callback(fn ($e) => $e instanceof \LogicException));

        $wallet = new AccountWallet([]);
        $spaceProject = new SpaceProject($this->createStub(Project::class));
        $spaceProject->addClusterEnv = 'prod';

        $this->assertInstanceOf(
            AddManagedEnvironmentToProject::class,
            ($this->addManagedEnvironmentToProject)(
                $manager,
                $spaceProject,
                $wallet,
                $this->createStub(ClusterCatalog::class),
            )
        );
    }

    public function testInvokeWithAccountEnvNotFound(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with($this->callback(fn ($e) => $e instanceof \DomainException));

        $wallet = new AccountWallet([]);
        $spaceProject = new SpaceProject($this->createStub(Project::class));
        $spaceProject->addClusterName = 'cluster1';
        $spaceProject->addClusterEnv = 'prod';

        $this->assertInstanceOf(
            AddManagedEnvironmentToProject::class,
            ($this->addManagedEnvironmentToProject)(
                $manager,
                $spaceProject,
                $wallet,
                $this->createStub(ClusterCatalog::class),
            )
        );
    }

    public function testInvokeWithClusterAdditionSuccess(): void
    {
        $accountEnv = $this->createStub(AccountEnvironment::class);
        $accountEnv
            ->method('getEnvName')
            ->willReturn('prod');
        $accountEnv
            ->method('getClusterName')
            ->willReturn('cluster1');
        $accountEnv
            ->method('getNamespace')
            ->willReturn('namespace1');
        $accountEnv
            ->method('getCaCertificate')
            ->willReturn('ca-cert');
        $accountEnv
            ->method('getClientCertificate')
            ->willReturn('client-cert');
        $accountEnv
            ->method('getClientKey')
            ->willReturn('client-key');
        $accountEnv
            ->method('getToken')
            ->willReturn('token');

        $wallet = new AccountWallet([$accountEnv]);

        $clusterConfig = new KubernetesCluster(
            name: 'cluster1',
            sluggyName: 'cluster1',
            type: 'kubernetes',
            masterAddress: 'https://cluster.example.com',
            storageProvisioner: 'standard',
            dashboardAddress: '',
            kubernetesClient: fn () => $this->createStub(Client::class),
            token: '',
            supportRegistry: false,
            useHnc: false,
            isExternal: false,
        );

        $clusterCatalog = $this->createMock(ClusterCatalog::class);
        $clusterCatalog->expects($this->once())
            ->method('getCluster')
            ->with('cluster1')
            ->willReturn($clusterConfig);

        $project = $this->createMock(Project::class);
        $project->expects($this->once())
            ->method('visit')
            ->with('clusters', $this->isCallable())
            ->willReturnCallback(
                function ($visitors, $callable) use ($project) {
                    $callable([]);

                    return $project;
                }
            );
        $project->expects($this->once())
            ->method('setClusters')
            ->with($this->isArray());

        $spaceProject = new SpaceProject($project);
        $spaceProject->addClusterName = 'cluster1';
        $spaceProject->addClusterEnv = 'prod';

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('error');

        $this->assertInstanceOf(
            AddManagedEnvironmentToProject::class,
            ($this->addManagedEnvironmentToProject)(
                $manager,
                $spaceProject,
                $wallet,
                $clusterCatalog,
            )
        );
    }

    public function testInvokeWithClusterAdditionSuccessWithIterator(): void
    {
        $accountEnv = $this->createStub(AccountEnvironment::class);
        $accountEnv
            ->method('getEnvName')
            ->willReturn('prod');
        $accountEnv
            ->method('getClusterName')
            ->willReturn('cluster1');
        $accountEnv
            ->method('getNamespace')
            ->willReturn('namespace1');
        $accountEnv
            ->method('getCaCertificate')
            ->willReturn('ca-cert');
        $accountEnv
            ->method('getClientCertificate')
            ->willReturn('client-cert');
        $accountEnv
            ->method('getClientKey')
            ->willReturn('client-key');
        $accountEnv
            ->method('getToken')
            ->willReturn('token');

        $wallet = new AccountWallet([$accountEnv]);

        $clusterConfig = new KubernetesCluster(
            name: 'cluster1',
            sluggyName: 'cluster1',
            type: 'kubernetes',
            masterAddress: 'https://cluster.example.com',
            storageProvisioner: 'standard',
            dashboardAddress: '',
            kubernetesClient: fn () => $this->createStub(Client::class),
            token: '',
            supportRegistry: false,
            useHnc: true,
            isExternal: false,
        );

        $clusterCatalog = $this->createMock(ClusterCatalog::class);
        $clusterCatalog->expects($this->once())
            ->method('getCluster')
            ->with('cluster1')
            ->willReturn($clusterConfig);

        $project = $this->createMock(Project::class);
        $project->expects($this->once())
            ->method('visit')
            ->with('clusters', $this->isCallable())
            ->willReturnCallback(function ($visitors, $callable) use ($project) {
                $callable(new \ArrayIterator([]));

                return $project;
            });
        $project->expects($this->once())
            ->method('setClusters')
            ->with($this->isArray());

        $spaceProject = new SpaceProject($project);
        $spaceProject->addClusterName = 'cluster1';
        $spaceProject->addClusterEnv = 'prod';

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('error');

        $this->assertInstanceOf(
            AddManagedEnvironmentToProject::class,
            ($this->addManagedEnvironmentToProject)(
                $manager,
                $spaceProject,
                $wallet,
                $clusterCatalog,
            )
        );
    }

    public function testInvokeAddsDockerComposeManagedEnvironmentWithSshIdentity(): void
    {
        // docker-compose environment: the SSH private key is stored in the AccountEnvironment `client_key`
        // field, the known_hosts host key in `ca_certificate`, and the token is empty (key-only, no password).
        $accountEnv = $this->createStub(AccountEnvironment::class);
        $accountEnv->method('getEnvName')->willReturn('prod');
        $accountEnv->method('getClusterName')->willReturn('docker-cluster');
        $accountEnv->method('getNamespace')->willReturn('acct-prod');
        $accountEnv->method('getCaCertificate')->willReturn('known-hosts-line');
        $accountEnv->method('getClientCertificate')->willReturn('');
        $accountEnv->method('getClientKey')->willReturn('-----BEGIN OPENSSH PRIVATE KEY-----KEY');
        $accountEnv->method('getToken')->willReturn('');

        $wallet = new AccountWallet([$accountEnv]);

        $clusterConfig = new DockerComposeCluster(
            name: 'docker-cluster',
            sluggyName: 'docker-cluster',
            type: 'docker-compose',
            masterAddress: 'ssh://deployer@docker-host.example.com:22',
            dashboardAddress: '',
            isExternal: true,
            clientKey: '-----BEGIN OPENSSH PRIVATE KEY-----KEY',
            caCertificate: 'known-hosts-line',
        );

        $clusterCatalog = $this->createMock(ClusterCatalog::class);
        $clusterCatalog->expects($this->once())
            ->method('getCluster')
            ->with('docker-cluster')
            ->willReturn($clusterConfig);

        $captured = null;
        $project = $this->createMock(Project::class);
        $project->expects($this->once())
            ->method('visit')
            ->with('clusters', $this->isCallable())
            ->willReturnCallback(function ($visitors, $callable) use ($project) {
                $callable([]);

                return $project;
            });
        $project->expects($this->once())
            ->method('setClusters')
            ->with($this->callback(function (array $clusters) use (&$captured): bool {
                $captured = $clusters[0] ?? null;

                return true;
            }));

        $spaceProject = new SpaceProject($project);
        $spaceProject->addClusterName = 'docker-cluster';
        $spaceProject->addClusterEnv = 'prod';

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');

        ($this->addManagedEnvironmentToProject)($manager, $spaceProject, $wallet, $clusterCatalog);

        // The docker-compose environment is added to the project as an East Cluster (its SSH ClusterCredentials
        // are built from the AccountEnvironment client_key/ca_certificate via the same generic mapping the
        // Kubernetes path uses; East Cluster exposes no public identity getter to assert deeper here).
        $this->assertInstanceOf(EastCluster::class, $captured);
    }

    public function testInvokeWithException(): void
    {
        $accountEnv = $this->createStub(AccountEnvironment::class);
        $accountEnv
            ->method('getEnvName')
            ->willReturn('prod');
        $accountEnv
            ->method('getClusterName')
            ->willReturn('cluster1');

        $wallet = new AccountWallet([$accountEnv]);

        $clusterCatalog = $this->createMock(ClusterCatalog::class);
        $clusterCatalog->expects($this->once())
            ->method('getCluster')
            ->with('cluster1')
            ->willThrowException(new \RuntimeException('Test error'));

        $spaceProject = new SpaceProject($this->createStub(Project::class));
        $spaceProject->addClusterName = 'cluster1';
        $spaceProject->addClusterEnv = 'prod';

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with($this->isInstanceOf(\Throwable::class));

        $this->assertInstanceOf(
            AddManagedEnvironmentToProject::class,
            ($this->addManagedEnvironmentToProject)(
                $manager,
                $spaceProject,
                $wallet,
                $clusterCatalog,
            )
        );
    }
}
