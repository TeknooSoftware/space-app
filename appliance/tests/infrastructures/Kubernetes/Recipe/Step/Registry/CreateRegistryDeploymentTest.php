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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Kubernetes\Recipe\Step\Registry;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Kubernetes\Client;
use Teknoo\Kubernetes\Collection\PodCollection;
use Teknoo\Kubernetes\Model\Pod;
use Teknoo\Kubernetes\Repository\PodRepository;
use Teknoo\Kubernetes\Repository\Repository;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Registry\CreateRegistryDeployment;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\ConfigClusterInterface;
use Teknoo\Space\Object\Config\Exception\UnsupportedClusterTypeException;
use Teknoo\Space\Object\Config\KubernetesCluster as ClusterConfig;
use Teknoo\Space\Object\Persisted\AccountHistory;

/**
 * Class CreateRegistryDeploymentTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(CreateRegistryDeployment::class)]
class CreateRegistryDeploymentTest extends TestCase
{
    private CreateRegistryDeployment $createRegistryAccount;

    private string $registryImageName;

    private string $registryCpuRequests;

    private string $registryMemoryRequests;

    private string $registryCpuLimits;

    private string $registryMemoryLimits;

    private string $tlsSecretName;

    private string $registryUrl;

    private string $clusterIssuer;

    private DatesService $datesService;

    private bool $preferRealDate;

    private string $ingressClass;

    private string $spaceRegistryUrl;

    private string $spaceRegistryUsername;

    private string $spaceRegistryPwd;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->registryImageName = '42';
        $this->registryCpuRequests = '42';
        $this->registryMemoryRequests = '42';
        $this->registryCpuLimits = '42';
        $this->registryMemoryLimits = '42';
        $this->tlsSecretName = '42';
        $this->registryUrl = '42';
        $this->clusterIssuer = '42';
        $this->datesService = (new DatesService())->setCurrentDate(new DateTimeImmutable('2024-01-01'));
        $this->preferRealDate = true;
        $this->ingressClass = '42';
        $this->spaceRegistryUrl = '42';
        $this->spaceRegistryUsername = '42';
        $this->spaceRegistryPwd = '42';
        $this->createRegistryAccount = new CreateRegistryDeployment(
            $this->registryImageName,
            $this->registryCpuRequests,
            $this->registryMemoryRequests,
            $this->registryCpuLimits,
            $this->registryMemoryLimits,
            $this->tlsSecretName,
            $this->registryUrl,
            $this->clusterIssuer,
            $this->datesService,
            $this->preferRealDate,
            $this->ingressClass,
            $this->spaceRegistryUrl,
            $this->spaceRegistryUsername,
            $this->spaceRegistryPwd
        );
    }

    private function createClusterConfig(Client $client): ClusterConfig
    {
        return new ClusterConfig(
            name: 'foo',
            sluggyName: 'foo',
            type: 'foo',
            masterAddress: 'foo',
            storageProvisioner: 'foo',
            dashboardAddress: 'foo',
            kubernetesClient: $client,
            token: 'foo',
            supportRegistry: true,
            useHnc: false,
            isExternal: false,
        );
    }

    public function testInvokeDeletesExistingPodsAndRecordsTheHistory(): void
    {
        $pod = new Pod(['metadata' => ['name' => 'bar-registry-pod']]);

        $collection = $this->createMock(PodCollection::class);
        $collection->expects($this->once())
            ->method('all')
            ->willReturn([$pod]);

        $podRepository = $this->createMock(PodRepository::class);
        $podRepository->expects($this->once())
            ->method('setLabelSelector')
            ->willReturnSelf();
        $podRepository->expects($this->once())
            ->method('find')
            ->willReturn($collection);
        $podRepository->expects($this->once())
            ->method('delete')
            ->with($pod)
            ->willReturn([]);

        $defaultRepository = $this->createStub(Repository::class);
        $client = $this->createStub(Client::class);
        $client->method('__call')
            ->willReturnCallback(
                fn (string $name): Repository => match ($name) {
                    'pods' => $podRepository,
                    default => $defaultRepository,
                }
            );

        $accountHistory = $this->createMock(AccountHistory::class);
        $accountHistory->expects($this->once())
            ->method('addToHistory')
            ->willReturnSelf();

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->willReturnSelf();
        $manager->expects($this->never())
            ->method('error');

        $this->assertInstanceOf(
            CreateRegistryDeployment::class,
            ($this->createRegistryAccount)(
                manager: $manager,
                kubeNamespace: 'foo',
                accountNamespace: 'bar',
                accountHistory: $accountHistory,
                persistentVolumeClaimName: 'foo',
                clusterCatalog: new ClusterCatalog(['defaults' => $this->createClusterConfig($client)], []),
            ),
        );
    }

    public function testInvokeReportsTheErrorToTheManager(): void
    {
        $error = new RuntimeException('boom');
        $client = $this->createStub(Client::class);
        $client->method('__call')
            ->willReturnCallback(
                fn (string $name): Repository => match ($name) {
                    'secrets' => throw $error,
                }
            );

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('updateWorkPlan');
        $manager->expects($this->once())
            ->method('error')
            ->with($error)
            ->willReturnSelf();

        $this->assertInstanceOf(
            CreateRegistryDeployment::class,
            ($this->createRegistryAccount)(
                manager: $manager,
                kubeNamespace: 'foo',
                accountNamespace: 'bar',
                accountHistory: $this->createStub(AccountHistory::class),
                persistentVolumeClaimName: 'foo',
                clusterCatalog: new ClusterCatalog(['defaults' => $this->createClusterConfig($client)], []),
            ),
        );
    }

    public function testInvokeThrowsOnNonKubernetesCluster(): void
    {
        $nonK8s = new class implements ConfigClusterInterface {
            public string $name = 'foo';

            public string $sluggyName = 'foo';

            public string $type = 'docker-compose';

            public string $masterAddress = 'ssh://u@h:22';

            public string $dashboardAddress = 'foo';

            public bool $supportRegistry = true;

            public bool $useHnc = false;

            public bool $isExternal = false;
        };

        $this->expectException(UnsupportedClusterTypeException::class);

        ($this->createRegistryAccount)(
            manager: $this->createStub(ManagerInterface::class),
            kubeNamespace: 'foo',
            accountNamespace: 'bar',
            accountHistory: $this->createStub(AccountHistory::class),
            persistentVolumeClaimName: 'foo',
            clusterCatalog: new ClusterCatalog(['defaults' => $nonK8s], []),
        );
    }
}
