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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Foundation\Time\SleepServiceInterface;
use Teknoo\Kubernetes\Client;
use Teknoo\Kubernetes\Repository\SecretRepository;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateSecretServiceAccountToken;
use Teknoo\Space\Object\Config\Cluster as ClusterConfig;
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

    private Client&MockObject $client;

    private DatesService&MockObject $datesService;

    private SleepServiceInterface&MockObject $sleepService;

    private int $secretWaitingTime;

    private bool $preferRealDate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createMock(Client::class);
        $this->client
            ->method('__call')
            ->willReturnCallback(
                fn (string $name): MockObject => match ($name) {
                    'secrets' => $this->createMock(SecretRepository::class),
                }
            );

        $this->datesService = $this->createMock(DatesService::class);
        $this->sleepService = $this->createMock(SleepServiceInterface::class);
        $this->secretWaitingTime = 42;
        $this->preferRealDate = true;
        $this->createSecret = new CreateSecretServiceAccountToken(
            $this->datesService,
            $this->sleepService,
            $this->secretWaitingTime,
            $this->preferRealDate
        );
    }

    public function testInvoke(): void
    {
        $clusterConfig = new ClusterConfig(
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

        $this->assertInstanceOf(
            CreateSecretServiceAccountToken::class,
            ($this->createSecret)(
                manager: $this->createMock(ManagerInterface::class),
                kubeNamespace: 'foo',
                accountNamespace: 'foo',
                serviceName: 'foo',
                accountHistory: $this->createMock(AccountHistory::class),
                clusterConfig: $clusterConfig,
            )
        );
    }
}
