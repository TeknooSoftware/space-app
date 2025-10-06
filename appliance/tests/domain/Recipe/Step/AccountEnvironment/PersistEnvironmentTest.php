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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\AccountEnvironment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Kubernetes\Client;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Object\Config\Cluster;
use Teknoo\Space\Object\Config\Cluster as ClusterConfig;
use Teknoo\Space\Object\DTO\AccountEnvironmentResume;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Recipe\Step\AccountEnvironment\PersistEnvironment;
use Teknoo\Space\Writer\AccountEnvironmentWriter;

/**
 * Class PersistCredentialsTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(PersistEnvironment::class)]
class PersistEnvironmentTest extends TestCase
{
    private PersistEnvironment $persistCredentials;

    private AccountEnvironmentWriter&MockObject $writer;

    private DatesService&MockObject $datesService;

    private string $clusterName;

    private bool $preferRealDate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->writer = $this->createMock(AccountEnvironmentWriter::class);
        $this->datesService = $this->createMock(DatesService::class);
        $this->clusterName = '42';
        $this->preferRealDate = true;
        $this->persistCredentials = new PersistEnvironment(
            $this->writer,
            $this->datesService,
            $this->preferRealDate,
        );
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            PersistEnvironment::class,
            actual: ($this->persistCredentials)(
                manager: $this->createMock(ManagerInterface::class),
                spaceAccount: new SpaceAccount($this->createMock(Account::class)),
                envName: 'foo',
                kubeNamespace: 'foo',
                serviceName: 'foo',
                roleName: 'foo',
                roleBindingName: 'foo',
                caCertificate: 'foo',
                token: 'foo',
                accountHistory: $this->createMock(AccountHistory::class),
                clusterConfig: new ClusterConfig(
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
                ),
                envMetadata: ['foo' => 'bar'],
            ),
        );
    }

    public function testInvokeWithAccountDirectly(): void
    {
        $account = $this->createMock(Account::class);

        $this->writer->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(function ($env) {
                    return $env instanceof AccountEnvironment;
                }),
                null
            );

        $this->datesService->expects($this->once())
            ->method('passMeTheDate')
            ->willReturnCallback(function ($callback, $preferRealDate) {
                $this->assertTrue($preferRealDate);
                $callback(new \DateTime());
                return $this->datesService;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan[AccountEnvironment::class])
                        && $workplan[AccountEnvironment::class] instanceof AccountEnvironment;
                })
            );
        $manager->expects($this->once())
            ->method('cleanWorkPlan')
            ->with('caCertificate', 'token', 'clientCertificate', 'clientKey');

        $this->assertInstanceOf(
            PersistEnvironment::class,
            ($this->persistCredentials)(
                manager: $manager,
                spaceAccount: $account,
                envName: 'test-env',
                kubeNamespace: 'test-ns',
                serviceName: 'test-svc',
                roleName: 'test-role',
                roleBindingName: 'test-rb',
                caCertificate: 'test-ca',
                token: 'test-token',
                accountHistory: $this->createMock(AccountHistory::class),
                clusterConfig: new ClusterConfig(
                    name: 'test-cluster',
                    sluggyName: 'test',
                    type: 'kubernetes',
                    masterAddress: 'https://test',
                    storageProvisioner: 'test',
                    dashboardAddress: 'https://dashboard',
                    kubernetesClient: $this->createMock(Client::class),
                    token: 'cluster-token',
                    supportRegistry: true,
                    useHnc: false,
                    isExternal: false,
                ),
            ),
        );
    }

    public function testInvokeWithResume(): void
    {
        $resume = new AccountEnvironmentResume(
            accountEnvironmentId: '',
            clusterName: 'test',
            envName: 'test',
        );

        $this->writer->expects($this->once())
            ->method('save')
            ->with(
                $this->isInstanceOf(AccountEnvironment::class),
                $this->isInstanceOf(PromiseInterface::class)
            )
            ->willReturnCallback(function ($env, $promise) use ($resume) {
                $env->setId('new-id-123');
                $promise->success($env);

                return $this->writer;
            });

        $this->datesService->expects($this->once())
            ->method('passMeTheDate')
            ->willReturnCallback(function ($callback) {
                $callback(new \DateTime());
                return $this->datesService;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan');
        $manager->expects($this->once())->method('cleanWorkPlan');

        $this->assertInstanceOf(
            PersistEnvironment::class,
            ($this->persistCredentials)(
                manager: $manager,
                spaceAccount: new SpaceAccount($this->createMock(Account::class)),
                envName: 'test-env',
                kubeNamespace: 'test-ns',
                serviceName: 'test-svc',
                roleName: 'test-role',
                roleBindingName: 'test-rb',
                caCertificate: 'test-ca',
                token: 'test-token',
                accountHistory: $this->createMock(AccountHistory::class),
                clusterConfig: new ClusterConfig(
                    name: 'test-cluster',
                    sluggyName: 'test',
                    type: 'kubernetes',
                    masterAddress: 'https://test',
                    storageProvisioner: 'test',
                    dashboardAddress: 'https://dashboard',
                    kubernetesClient: $this->createMock(Client::class),
                    token: 'cluster-token',
                    supportRegistry: true,
                    useHnc: false,
                    isExternal: false,
                ),
                resume: $resume,
            ),
        );

        $this->assertEquals('new-id-123', $resume->accountEnvironmentId);
    }

    public function testInvokeWithPreferRealDateFalse(): void
    {
        $persistEnvironment = new PersistEnvironment(
            $this->writer,
            $this->datesService,
            false
        );

        $this->datesService->expects($this->once())
            ->method('passMeTheDate')
            ->willReturnCallback(function ($callback, $preferRealDate) {
                $this->assertFalse($preferRealDate);
                $callback(new \DateTime());
                return $this->datesService;
            });

        $this->writer->expects($this->once())->method('save');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan');
        $manager->expects($this->once())->method('cleanWorkPlan');

        $this->assertInstanceOf(
            PersistEnvironment::class,
            ($persistEnvironment)(
                manager: $manager,
                spaceAccount: $this->createMock(Account::class),
                envName: 'test-env',
                kubeNamespace: 'test-ns',
                serviceName: 'test-svc',
                roleName: 'test-role',
                roleBindingName: 'test-rb',
                caCertificate: 'test-ca',
                token: 'test-token',
                accountHistory: $this->createMock(AccountHistory::class),
                clusterConfig: new ClusterConfig(
                    name: 'test-cluster',
                    sluggyName: 'test',
                    type: 'kubernetes',
                    masterAddress: 'https://test',
                    storageProvisioner: 'test',
                    dashboardAddress: 'https://dashboard',
                    kubernetesClient: $this->createMock(Client::class),
                    token: 'cluster-token',
                    supportRegistry: true,
                    useHnc: false,
                    isExternal: false,
                ),
            ),
        );
    }

    public function testInvokeWithAccountHistoryCallback(): void
    {
        $accountHistory = $this->createMock(AccountHistory::class);
        $accountHistory->expects($this->once())
            ->method('addToHistory')
            ->with(
                'teknoo.space.text.account.kubernetes.credential_persisted',
                $this->isInstanceOf(\DateTimeInterface::class)
            );

        $this->datesService->expects($this->once())
            ->method('passMeTheDate')
            ->willReturnCallback(function ($callback) {
                $callback(new \DateTime('2025-01-01 12:00:00'));
                return $this->datesService;
            });

        $this->writer->expects($this->once())->method('save');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan');
        $manager->expects($this->once())->method('cleanWorkPlan');

        $this->assertInstanceOf(
            PersistEnvironment::class,
            ($this->persistCredentials)(
                manager: $manager,
                spaceAccount: $this->createMock(Account::class),
                envName: 'test-env',
                kubeNamespace: 'test-ns',
                serviceName: 'test-svc',
                roleName: 'test-role',
                roleBindingName: 'test-rb',
                caCertificate: 'test-ca',
                token: 'test-token',
                accountHistory: $accountHistory,
                clusterConfig: new ClusterConfig(
                    name: 'test-cluster',
                    sluggyName: 'test',
                    type: 'kubernetes',
                    masterAddress: 'https://test',
                    storageProvisioner: 'test',
                    dashboardAddress: 'https://dashboard',
                    kubernetesClient: $this->createMock(Client::class),
                    token: 'cluster-token',
                    supportRegistry: true,
                    useHnc: false,
                    isExternal: false,
                ),
            ),
        );
    }

    public function testInvokeWithEmptyMetadata(): void
    {
        $this->writer->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(function ($env) {
                    return $env instanceof AccountEnvironment;
                }),
                null
            );

        $this->datesService->expects($this->once())
            ->method('passMeTheDate')
            ->willReturnCallback(function ($callback) {
                $callback(new \DateTime());
                return $this->datesService;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan');
        $manager->expects($this->once())->method('cleanWorkPlan');

        $this->assertInstanceOf(
            PersistEnvironment::class,
            ($this->persistCredentials)(
                manager: $manager,
                spaceAccount: $this->createMock(Account::class),
                envName: 'test-env',
                kubeNamespace: 'test-ns',
                serviceName: 'test-svc',
                roleName: 'test-role',
                roleBindingName: 'test-rb',
                caCertificate: 'test-ca',
                token: 'test-token',
                accountHistory: $this->createMock(AccountHistory::class),
                clusterConfig: new ClusterConfig(
                    name: 'test-cluster',
                    sluggyName: 'test',
                    type: 'kubernetes',
                    masterAddress: 'https://test',
                    storageProvisioner: 'test',
                    dashboardAddress: 'https://dashboard',
                    kubernetesClient: $this->createMock(Client::class),
                    token: 'cluster-token',
                    supportRegistry: true,
                    useHnc: false,
                    isExternal: false,
                ),
                envMetadata: [],
            ),
        );
    }
}
