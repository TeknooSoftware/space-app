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
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Kubernetes\Client;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateQuota;
use Teknoo\Space\Object\Config\Cluster as ClusterConfig;
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

    private DatesService&Stub $datesService;

    private bool $preferRealDate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->datesService = $this->createStub(DatesService::class);
        $this->preferRealDate = true;
        $this->createQuota = new CreateQuota($this->datesService, $this->preferRealDate);
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
            kubernetesClient: $this->createStub(Client::class),
            token: 'foo',
            supportRegistry: true,
            useHnc: false,
            isExternal: false,
        );

        $this->assertInstanceOf(
            CreateQuota::class,
            ($this->createQuota)(
                manager: $this->createStub(ManagerInterface::class),
                kubeNamespace: 'foo',
                accountNamespace: 'foo',
                accountInstance: $this->createStub(Account::class),
                accountHistory: $this->createStub(AccountHistory::class),
                clusterConfig: $clusterConfig,
            )
        );
    }
}
