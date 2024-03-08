<?php

/*
 * Teknoo Space.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Infrastructures\Kubernetes\Recipe\Step\Account;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Kubernetes\Client;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateRegistryAccount;
use Teknoo\Space\Object\Config\Cluster as ClusterConfig;
use Teknoo\Space\Object\Persisted\AccountHistory;

/**
 * Class CreateRegistryAccountTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateRegistryAccount
 */
class CreateRegistryAccountTest extends TestCase
{
    private CreateRegistryAccount $createRegistryAccount;

    private string $registryImageName;

    private string $registryCpuRequests;

    private string $registryMemoryRequests;

    private string $registryCpuLimits;

    private string $registryMemoryLimits;

    private string $tlsSecretName;

    private string $registryUrl;

    private string $clusterIssuer;

    private DatesService|MockObject $datesService;

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
        $this->datesService = $this->createMock(DatesService::class);
        $this->preferRealDate = true;
        $this->ingressClass = '42';
        $this->spaceRegistryUrl = '42';
        $this->spaceRegistryUsername = '42';
        $this->spaceRegistryPwd = '42';
        $this->createRegistryAccount = new CreateRegistryAccount(
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

    public function testInvoke(): void
    {
        $clusterConfig = new ClusterConfig(
            name: 'foo',
            sluggyName: 'foo',
            type: 'foo',
            masterAddress: 'foo',
            defaultEnv: 'foo',
            storageProvisioner: 'foo',
            dashboardAddress: 'foo',
            kubernetesClient: $this->createMock(Client::class),
            token: 'foo',
        );

        self::assertInstanceOf(
            CreateRegistryAccount::class,
            ($this->createRegistryAccount)(
                manager: $this->createMock(ManagerInterface::class),
                kubeNamespace: 'foo',
                accountNamespace: 'bar',
                accountHistory: $this->createMock(AccountHistory::class),
                persistentVolumeClaimName: 'foo',
                clusterConfig: $clusterConfig,
            ),
        );
    }
}
