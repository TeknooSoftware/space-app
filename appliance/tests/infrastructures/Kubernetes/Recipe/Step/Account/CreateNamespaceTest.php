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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Kubernetes\Recipe\Step\Account;

use DateTimeImmutable;
use DateTimeInterface;
use DomainException;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Kubernetes\Client;
use Teknoo\Kubernetes\Model\NamespaceModel;
use Teknoo\Kubernetes\Repository\NamespaceRepository;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateNamespace;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\ConfigClusterInterface;
use Teknoo\Space\Object\Config\DockerComposeCluster;
use Teknoo\Space\Object\Config\Exception\UnsupportedClusterTypeException;
use Teknoo\Space\Object\Config\KubernetesCluster as ClusterConfig;
use Teknoo\Space\Object\Persisted\AccountHistory;

/**
 * Class CreateNamespaceTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(CreateNamespace::class)]
class CreateNamespaceTest extends TestCase
{
    private CreateNamespace $createNamespace;

    private string $rootNamespace;

    private string $registryRootNamespace;

    private DatesService $datesService;

    private bool $preferRealDate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->rootNamespace = '42';
        $this->registryRootNamespace = '42';
        $this->datesService = (new DatesService())->setCurrentDate(new DateTimeImmutable('2024-01-01'));
        $this->preferRealDate = true;

        $this->createNamespace = new CreateNamespace(
            $this->rootNamespace,
            $this->registryRootNamespace,
            $this->datesService,
            $this->preferRealDate,
        );
    }

    private function createClusterConfig(NamespaceRepository&MockObject $repository): ClusterConfig
    {
        $client = $this->createStub(Client::class);
        $client->method('__call')
            ->willReturnCallback(
                fn (string $name): NamespaceRepository => match ($name) {
                    'namespaces' => $repository,
                }
            );

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

    private function createRepository(?NamespaceModel $existing, bool $applied): NamespaceRepository&MockObject
    {
        $repository = $this->createMock(NamespaceRepository::class);
        $repository->expects($this->once())
            ->method('setFieldSelector')
            ->willReturnSelf();
        $repository->expects($this->once())
            ->method('first')
            ->willReturn($existing);
        $repository->expects($applied ? $this->once() : $this->never())
            ->method('apply')
            ->willReturn([]);

        return $repository;
    }

    private function createAccount(): Account
    {
        return (new Account())->setId('acc-1')->setName('Acct');
    }

    public function testInvokeForRegistry(): void
    {
        $accountHistory = $this->createMock(AccountHistory::class);
        $accountHistory->expects($this->once())
            ->method('addToHistory')
            ->with(
                'teknoo.space.text.account.kubernetes.namespace',
                $this->isInstanceOf(DateTimeInterface::class),
                false,
                ['namespace' => '42foo', 'for-registry' => 'true'],
            )
            ->willReturnSelf();

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(['kubeNamespace' => '42foo'])
            ->willReturnSelf();

        $this->assertInstanceOf(
            CreateNamespace::class,
            ($this->createNamespace)(
                manager: $manager,
                accountInstance: $this->createAccount(),
                accountHistory: $accountHistory,
                accountNamespace: 'foo',
                clusterCatalog: new ClusterCatalog(
                    ['default' => $this->createClusterConfig($this->createRepository(null, true))],
                    [],
                ),
                forRegistry: true,
            ),
        );
    }

    public function testInvokeForEnvironmentWithAnOwnedNamespace(): void
    {
        $accountHistory = $this->createMock(AccountHistory::class);
        $accountHistory->expects($this->once())
            ->method('addToHistory')
            ->with(
                'teknoo.space.text.account.kubernetes.namespace',
                $this->isInstanceOf(DateTimeInterface::class),
                false,
                ['namespace' => '42foo-prod', 'for-registry' => 'false'],
            )
            ->willReturnSelf();

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(['kubeNamespace' => '42foo-prod'])
            ->willReturnSelf();

        $existing = new NamespaceModel(['metadata' => ['name' => '42foo-prod', 'labels' => ['id' => 'acc-1']]]);

        $this->assertInstanceOf(
            CreateNamespace::class,
            ($this->createNamespace)(
                manager: $manager,
                accountInstance: $this->createAccount(),
                accountHistory: $accountHistory,
                accountNamespace: 'foo',
                clusterCatalog: new ClusterCatalog(
                    ['default' => $this->createClusterConfig($this->createRepository($existing, true))],
                    [],
                ),
                forRegistry: false,
                clusterName: 'default',
                envName: 'Prod',
            ),
        );
    }

    public function testInvokeThrowsWhenTheNamespaceIsOwnedByAnotherAccount(): void
    {
        $existing = new NamespaceModel(['metadata' => ['name' => '42foo-prod', 'labels' => ['id' => 'acc-2']]]);

        $this->expectException(DomainException::class);

        ($this->createNamespace)(
            manager: $this->createStub(ManagerInterface::class),
            accountInstance: $this->createAccount(),
            accountHistory: $this->createStub(AccountHistory::class),
            accountNamespace: 'foo',
            clusterCatalog: new ClusterCatalog(
                ['default' => $this->createClusterConfig($this->createRepository($existing, false))],
                [],
            ),
            forRegistry: false,
            clusterName: 'default',
            envName: 'Prod',
        );
    }

    public function testInvokeThrowsWithoutClusterNameForEnvironment(): void
    {
        $this->expectException(LogicException::class);

        ($this->createNamespace)(
            manager: $this->createStub(ManagerInterface::class),
            accountInstance: $this->createAccount(),
            accountHistory: $this->createStub(AccountHistory::class),
            accountNamespace: 'foo',
            clusterCatalog: new ClusterCatalog([], []),
            forRegistry: false,
            clusterName: null,
            envName: 'Prod',
        );
    }

    public function testInvokeThrowsOnNonKubernetesClusterForRegistry(): void
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

        ($this->createNamespace)(
            manager: $this->createStub(ManagerInterface::class),
            accountInstance: $this->createStub(Account::class),
            accountHistory: $this->createStub(AccountHistory::class),
            accountNamespace: 'foo',
            clusterCatalog: new ClusterCatalog(['default' => $nonK8s], []),
            forRegistry: true,
        );
    }

    public function testInvokeThrowsOnNonKubernetesClusterForNamespace(): void
    {
        $dockerCompose = new DockerComposeCluster(
            name: 'foo',
            sluggyName: 'foo',
            type: 'docker-compose',
            masterAddress: 'ssh://u@h:22',
            dashboardAddress: 'foo',
            isExternal: false,
            clientKey: 'k',
        );

        $this->expectException(UnsupportedClusterTypeException::class);

        ($this->createNamespace)(
            manager: $this->createStub(ManagerInterface::class),
            accountInstance: $this->createStub(Account::class),
            accountHistory: $this->createStub(AccountHistory::class),
            accountNamespace: 'foo',
            clusterCatalog: new ClusterCatalog(['default' => $dockerCompose], []),
            forRegistry: false,
            clusterName: 'default',
            envName: 'foo',
        );
    }
}
