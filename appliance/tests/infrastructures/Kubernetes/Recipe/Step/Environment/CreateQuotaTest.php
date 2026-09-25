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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Kubernetes\Recipe\Step\Environment;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\AccountQuota;
use Teknoo\Kubernetes\Client;
use Teknoo\Kubernetes\Model\ResourceQuota;
use Teknoo\Kubernetes\Repository\ResourceQuotaRepository;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateQuota;
use Teknoo\Space\Object\Config\DockerComposeCluster;
use Teknoo\Space\Object\Config\Exception\UnsupportedClusterTypeException;
use Teknoo\Space\Object\Config\KubernetesCluster as ClusterConfig;
use Teknoo\Space\Object\Persisted\AccountHistory;

/**
 * Class CreateQuotaTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(CreateQuota::class)]
class CreateQuotaTest extends TestCase
{
    private CreateQuota $createQuota;

    private DatesService $datesService;

    private bool $preferRealDate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->datesService = (new DatesService())->setCurrentDate(new DateTimeImmutable('2024-01-01'));
        $this->preferRealDate = true;
        $this->createQuota = new CreateQuota($this->datesService, $this->preferRealDate);
    }

    private function createClusterConfig(ResourceQuotaRepository&MockObject $repository): ClusterConfig
    {
        $client = $this->createStub(Client::class);
        $client->method('__call')
            ->willReturnCallback(
                fn (string $name): ResourceQuotaRepository => match ($name) {
                    'resourceQuotas' => $repository,
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

    private function createAccountWithQuotas(): Account
    {
        return (new Account())->setName('foo')->setQuotas([
            new AccountQuota('compute', 'cpu', '2', '1'),
            new AccountQuota('storage', 'storage', '10Gi', '5Gi'),
            new AccountQuota('count', 'pods', '10', '5'),
        ]);
    }

    public function testInvokeCreatesTheQuota(): void
    {
        $repository = $this->createMock(ResourceQuotaRepository::class);
        $repository->expects($this->once())
            ->method('exists')
            ->with('foo-quota')
            ->willReturn(false);
        $repository->expects($this->once())
            ->method('create')
            ->with($this->callback(
                fn (ResourceQuota $model): bool => $model->toArray()['spec']['hard'] === [
                    'requests.cpu' => '1',
                    'limits.cpu' => '2',
                    'requests.storage' => '5Gi',
                    'pods' => '5',
                ]
            ))
            ->willReturn([]);
        $repository->expects($this->never())
            ->method('update');

        $accountHistory = $this->createMock(AccountHistory::class);
        $accountHistory->expects($this->once())
            ->method('addToHistory')
            ->willReturnSelf();

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(['quotaName' => 'foo-quota'])
            ->willReturnSelf();

        $this->assertInstanceOf(
            CreateQuota::class,
            ($this->createQuota)(
                manager: $manager,
                kubeNamespace: 'foo',
                accountNamespace: 'foo',
                accountInstance: $this->createAccountWithQuotas(),
                accountHistory: $accountHistory,
                clusterConfig: $this->createClusterConfig($repository),
            )
        );
    }

    public function testInvokeUpdatesTheExistingQuota(): void
    {
        $repository = $this->createMock(ResourceQuotaRepository::class);
        $repository->expects($this->once())
            ->method('exists')
            ->with('foo-quota')
            ->willReturn(true);
        $repository->expects($this->never())
            ->method('create');
        $repository->expects($this->once())
            ->method('update')
            ->willReturn(['status' => 'Success']);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(['quotaName' => 'foo-quota'])
            ->willReturnSelf();

        $this->assertInstanceOf(
            CreateQuota::class,
            ($this->createQuota)(
                manager: $manager,
                kubeNamespace: 'foo',
                accountNamespace: 'foo',
                accountInstance: $this->createAccountWithQuotas(),
                accountHistory: $this->createStub(AccountHistory::class),
                clusterConfig: $this->createClusterConfig($repository),
            )
        );
    }

    public function testInvokeWithoutQuotasDoesNothing(): void
    {
        $repository = $this->createMock(ResourceQuotaRepository::class);
        $repository->expects($this->never())
            ->method('exists');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('updateWorkPlan');

        $this->assertInstanceOf(
            CreateQuota::class,
            ($this->createQuota)(
                manager: $manager,
                kubeNamespace: 'foo',
                accountNamespace: 'foo',
                accountInstance: (new Account())->setName('foo')->setQuotas([]),
                accountHistory: $this->createStub(AccountHistory::class),
                clusterConfig: $this->createClusterConfig($repository),
            )
        );
    }

    public function testInvokeThrowsOnNonKubernetesCluster(): void
    {
        $this->expectException(UnsupportedClusterTypeException::class);

        ($this->createQuota)(
            manager: $this->createStub(ManagerInterface::class),
            kubeNamespace: 'foo',
            accountNamespace: 'foo',
            accountInstance: $this->createStub(Account::class),
            accountHistory: $this->createStub(AccountHistory::class),
            clusterConfig: new DockerComposeCluster(
                name: 'foo',
                sluggyName: 'foo',
                type: 'docker-compose',
                masterAddress: 'ssh://u@h:22',
                dashboardAddress: 'foo',
                isExternal: false,
                clientKey: 'k',
            ),
        );
    }
}
