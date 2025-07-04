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
 * @link        https://teknoo.software/applications/space Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Infrastructures\Kubernetes\Recipe\Step\Account;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Kubernetes\Client;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateNamespace;
use Teknoo\Space\Object\Config\Cluster as ClusterConfig;
use Teknoo\Space\Object\Config\ClusterCatalog;
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

    private DatesService|MockObject $datesService;

    private bool $preferRealDate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->rootNamespace = '42';
        $this->registryRootNamespace = '42';
        $this->datesService = $this->createMock(DatesService::class);
        $this->preferRealDate = true;

        $this->createNamespace = new CreateNamespace(
            $this->rootNamespace,
            $this->registryRootNamespace,
            $this->datesService,
            $this->preferRealDate,
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
            kubernetesClient: $this->createMock(Client::class),
            token: 'foo',
            supportRegistry: true,
            useHnc: false,
            isExternal: false,
        );

        self::assertInstanceOf(
            CreateNamespace::class,
            ($this->createNamespace)(
                manager: $this->createMock(ManagerInterface::class),
                accountInstance: $this->createMock(Account::class),
                accountHistory: $this->createMock(AccountHistory::class),
                accountNamespace: 'foo',
                clusterCatalog: new ClusterCatalog(['default' => $clusterConfig], []),
                forRegistry: true,
            ),
        );
    }
}
