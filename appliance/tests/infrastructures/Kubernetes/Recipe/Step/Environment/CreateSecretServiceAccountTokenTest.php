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
use DateTimeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Foundation\Time\SleepServiceInterface;
use Teknoo\Kubernetes\Client;
use Teknoo\Kubernetes\Model\Secret;
use Teknoo\Kubernetes\Repository\SecretRepository;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateSecretServiceAccountToken;
use Teknoo\Space\Object\Config\DockerComposeCluster;
use Teknoo\Space\Object\Config\Exception\UnsupportedClusterTypeException;
use Teknoo\Space\Object\Config\KubernetesCluster as ClusterConfig;
use Teknoo\Space\Object\Persisted\AccountHistory;

/**
 * Class CreateSecretTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(CreateSecretServiceAccountToken::class)]
class CreateSecretServiceAccountTokenTest extends TestCase
{
    private CreateSecretServiceAccountToken $createSecret;

    private Client&Stub $client;

    private SecretRepository&Stub $secretRepository;

    private DatesService $datesService;

    private SleepServiceInterface&Stub $sleepService;

    private int $secretWaitingTime;

    private bool $preferRealDate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->secretRepository = $this->createStub(SecretRepository::class);
        $this->client = $this->createStub(Client::class);
        $this->client
            ->method('__call')
            ->willReturnCallback(
                fn (string $name): Stub => match ($name) {
                    'secrets' => $this->secretRepository,
                }
            );

        $this->datesService = (new DatesService())->setCurrentDate(new DateTimeImmutable('2024-01-01'));
        $this->sleepService = $this->createStub(SleepServiceInterface::class);
        $this->secretWaitingTime = 42;
        $this->preferRealDate = true;
        $this->createSecret = new CreateSecretServiceAccountToken(
            $this->datesService,
            $this->sleepService,
            $this->secretWaitingTime,
            $this->preferRealDate
        );
    }

    private function createClusterConfig(): ClusterConfig
    {
        return new ClusterConfig(
            name: 'foo',
            sluggyName: 'foo',
            type: 'foo',
            masterAddress: 'foo',
            storageProvisioner: 'foo',
            dashboardAddress: 'foo',
            kubernetesClient: $this->client,
            token: 'foo',
            supportRegistry: true,
            useHnc: false,
            isExternal: false,
        );
    }

    public function testInvokeWhenTheSecretIsNeverAvailable(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->willReturnSelf();
        $manager->expects($this->never())
            ->method('updateWorkPlan');

        $this->assertInstanceOf(
            CreateSecretServiceAccountToken::class,
            ($this->createSecret)(
                manager: $manager,
                kubeNamespace: 'foo',
                accountNamespace: 'foo',
                serviceName: 'foo',
                accountHistory: $this->createStub(AccountHistory::class),
                clusterConfig: $this->createClusterConfig(),
            )
        );
    }

    public function testInvokeWhenTheSecretIsAvailable(): void
    {
        $this->secretRepository->method('exists')->willReturn(true);
        $this->secretRepository->method('setLabelSelector')->willReturnSelf();
        $this->secretRepository->method('first')->willReturn(
            new Secret([
                'metadata' => ['name' => 'foo-secret'],
                'data' => [
                    'token' => base64_encode('a-token'),
                    'ca.crt' => base64_encode('a-ca'),
                ],
            ])
        );

        $accountHistory = $this->createMock(AccountHistory::class);
        $accountHistory->expects($this->once())
            ->method('addToHistory')
            ->with('teknoo.space.text.account.kubernetes.secret', $this->isInstanceOf(DateTimeInterface::class))
            ->willReturnSelf();

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('error');
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(['token' => 'a-token', 'caCertificate' => 'a-ca'])
            ->willReturnSelf();

        $this->assertInstanceOf(
            CreateSecretServiceAccountToken::class,
            ($this->createSecret)(
                manager: $manager,
                kubeNamespace: 'foo',
                accountNamespace: 'foo',
                serviceName: 'foo',
                accountHistory: $accountHistory,
                clusterConfig: $this->createClusterConfig(),
            )
        );
    }

    public function testInvokeThrowsOnNonKubernetesCluster(): void
    {
        $this->expectException(UnsupportedClusterTypeException::class);

        ($this->createSecret)(
            manager: $this->createStub(ManagerInterface::class),
            kubeNamespace: 'foo',
            accountNamespace: 'foo',
            serviceName: 'foo',
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
