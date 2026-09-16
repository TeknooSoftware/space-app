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
namespace Teknoo\Space\Tests\Unit\Recipe\Step\AccountRegistry;

use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\ConfigClusterInterface;
use Teknoo\Space\Object\Persisted\AccountRegistry;
use Teknoo\Space\Recipe\Step\AccountRegistry\SelectRegistryCluster;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(SelectRegistryCluster::class)]
class SelectRegistryClusterTest extends TestCase
{
    private function cluster(string $name, bool $supportRegistry): ConfigClusterInterface
    {
        return new class ($name, $supportRegistry) implements ConfigClusterInterface {
            public string $sluggyName = 'slug';

            public string $type = 'kubernetes';

            public string $masterAddress = '';

            public string $dashboardAddress = '';

            public bool $useHnc = false;

            public bool $isExternal = false;

            public function __construct(
                public string $name,
                public bool $supportRegistry,
            ) {
            }
        };
    }

    public function testWithoutRegistryItSelectsTheFirstClusterSupportingTheRegistry(): void
    {
        $catalog = new ClusterCatalog(
            [
                'Without' => $this->cluster('Without', false),
                'Registry Cluster' => $this->cluster('Registry Cluster', true),
            ],
            [],
        );

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(['registryClusterName' => 'Registry Cluster'])
            ->willReturnSelf();

        $step = new SelectRegistryCluster();

        $this->assertInstanceOf(SelectRegistryCluster::class, $step($manager, $catalog));
    }

    public function testWithARegistryItSelectsTheRecordedCluster(): void
    {
        $catalog = new ClusterCatalog(
            [
                'First' => $this->cluster('First', true),
                'Recorded' => $this->cluster('Recorded', true),
            ],
            [],
        );

        $registry = $this->createMock(AccountRegistry::class);
        $registry->expects($this->once())
            ->method('getClusterName')
            ->willReturn('Recorded');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(['registryClusterName' => 'Recorded'])
            ->willReturnSelf();

        $step = new SelectRegistryCluster();

        $this->assertInstanceOf(SelectRegistryCluster::class, $step($manager, $catalog, $registry));
    }

    public function testARecordedAliasIsPublishedAsTheCanonicalClusterName(): void
    {
        $catalog = new ClusterCatalog(
            ['Recorded' => $this->cluster('Recorded', true)],
            ['recorded-slug' => 'Recorded'],
        );

        $registry = $this->createMock(AccountRegistry::class);
        $registry->expects($this->once())
            ->method('getClusterName')
            ->willReturn('recorded-slug');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(['registryClusterName' => 'Recorded'])
            ->willReturnSelf();

        $step = new SelectRegistryCluster();

        $this->assertInstanceOf(SelectRegistryCluster::class, $step($manager, $catalog, $registry));
    }

    public function testALegacyRegistryWithoutClusterNameFallsBackToTheFirstRegistryCluster(): void
    {
        $catalog = new ClusterCatalog(
            ['Registry Cluster' => $this->cluster('Registry Cluster', true)],
            [],
        );

        $registry = $this->createMock(AccountRegistry::class);
        $registry->expects($this->once())
            ->method('getClusterName')
            ->willReturn(null);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(['registryClusterName' => 'Registry Cluster'])
            ->willReturnSelf();

        $step = new SelectRegistryCluster();

        $this->assertInstanceOf(SelectRegistryCluster::class, $step($manager, $catalog, $registry));
    }

    public function testARecordedClusterMissingFromTheCatalogFailsInsteadOfMovingTheRegistry(): void
    {
        $catalog = new ClusterCatalog(
            ['Registry Cluster' => $this->cluster('Registry Cluster', true)],
            [],
        );

        $registry = $this->createMock(AccountRegistry::class);
        $registry->expects($this->once())
            ->method('getClusterName')
            ->willReturn('Deleted Cluster');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('updateWorkPlan');

        $step = new SelectRegistryCluster();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cluster Deleted Cluster is not available in the catalog');

        $step($manager, $catalog, $registry);
    }
}
