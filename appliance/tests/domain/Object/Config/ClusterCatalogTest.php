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

namespace Teknoo\Space\Tests\Unit\Object\Config;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Kubernetes\Client;
use Teknoo\Space\Object\Config\Cluster;
use Teknoo\Space\Object\Config\ClusterCatalog;

use function iterator_to_array;

/**
 * Class SearchTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ClusterCatalog::class)]
class ClusterCatalogTest extends TestCase
{
    private ClusterCatalog $clusterCatalog;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->clusterCatalog = new ClusterCatalog(
            ['Foo' => $this->createMock(Cluster::class)],
            ['foo' => 'Foo'],
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            Cluster::class,
            iterator_to_array($this->clusterCatalog)['Foo'],
        );

        $this->assertInstanceOf(
            Cluster::class,
            $this->clusterCatalog->getCluster('foo'),
        );
    }

    public function testGetClusterForRegistry(): void
    {
        $cluster = new Cluster(
            'test',
            'test',
            'test',
            'https://test',
            'test',
            'https://test',
            $this->createMock(Client::class),
            'token',
            true,
            false,
            false,
        );

        $catalog = new ClusterCatalog(
            ['TestCluster' => $cluster],
            [],
        );

        $this->assertSame($cluster, $catalog->getClusterForRegistry());
    }

    public function testGetClusterForRegistryWithNoSupportThrowsExceptionEvenWithParent(): void
    {
        $clusterWithRegistry = new Cluster(
            'parent',
            'parent',
            'test',
            'https://test',
            'test',
            'https://test',
            $this->createMock(Client::class),
            'token',
            true,
            false,
            false,
        );

        $parentCatalog = new ClusterCatalog(
            ['ParentCluster' => $clusterWithRegistry],
            [],
        );

        $childCluster = new Cluster(
            'child',
            'child',
            'test',
            'https://test',
            'test',
            'https://test',
            $this->createMock(Client::class),
            'token',
            false,
            false,
            false,
        );

        $catalog = new ClusterCatalog(
            ['ChildCluster' => $childCluster],
            [],
            $parentCatalog,
        );

        // The current implementation doesn't return from parent, so exception is thrown
        $this->expectException(\DomainException::class);
        $catalog->getClusterForRegistry();
    }

    public function testGetClusterForRegistryThrowsException(): void
    {
        $cluster = new Cluster(
            'test',
            'test',
            'test',
            'https://test',
            'test',
            'https://test',
            $this->createMock(Client::class),
            'token',
            false,
            false,
            false,
        );

        $catalog = new ClusterCatalog(
            ['TestCluster' => $cluster],
            [],
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Missing cluster configuration able to support privates registries');
        $catalog->getClusterForRegistry();
    }

    public function testGetDefaultClusterName(): void
    {
        $cluster = $this->createMock(Cluster::class);

        $catalog = new ClusterCatalog(
            ['DefaultCluster' => $cluster],
            [],
        );

        $this->assertSame('DefaultCluster', $catalog->getDefaultClusterName());
    }

    public function testGetDefaultClusterNameWithEmptyClustersThrowsExceptionEvenWithParent(): void
    {
        $parentCluster = $this->createMock(Cluster::class);
        $parentCatalog = new ClusterCatalog(
            ['ParentCluster' => $parentCluster],
            [],
        );

        $catalog = new ClusterCatalog(
            [],
            [],
            $parentCatalog,
        );

        // The current implementation doesn't return from parent, so exception is thrown
        $this->expectException(\DomainException::class);
        $catalog->getDefaultClusterName();
    }

    public function testGetDefaultClusterNameThrowsException(): void
    {
        $catalog = new ClusterCatalog(
            [],
            [],
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Missing cluster configuration able to support privates registries');
        $catalog->getDefaultClusterName();
    }

    public function testGetClusterWithEastCluster(): void
    {
        $cluster = $this->createMock(Cluster::class);
        $eastCluster = $this->createMock(\Teknoo\East\Paas\Object\Cluster::class);
        $eastCluster->expects($this->any())
            ->method('__toString')
            ->willReturn('TestCluster');

        $catalog = new ClusterCatalog(
            ['TestCluster' => $cluster],
            [],
        );

        $this->assertSame($cluster, $catalog->getCluster($eastCluster));
    }

    public function testGetClusterDirectName(): void
    {
        $cluster = $this->createMock(Cluster::class);

        $catalog = new ClusterCatalog(
            ['DirectCluster' => $cluster],
            [],
        );

        $this->assertSame($cluster, $catalog->getCluster('DirectCluster'));
    }

    public function testGetClusterWithParent(): void
    {
        $parentCluster = $this->createMock(Cluster::class);
        $parentCatalog = new ClusterCatalog(
            ['ParentCluster' => $parentCluster],
            [],
        );

        $catalog = new ClusterCatalog(
            ['ChildCluster' => $this->createMock(Cluster::class)],
            [],
            $parentCatalog,
        );

        $this->assertSame($parentCluster, $catalog->getCluster('ParentCluster'));
    }

    public function testGetClusterThrowsException(): void
    {
        $catalog = new ClusterCatalog(
            ['TestCluster' => $this->createMock(Cluster::class)],
            [],
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cluster NonExistent is not available in the catalog');
        $catalog->getCluster('NonExistent');
    }

    public function testGetIteratorWithParent(): void
    {
        $parentCluster = $this->createMock(Cluster::class);
        $parentCatalog = new ClusterCatalog(
            ['ParentCluster' => $parentCluster],
            [],
        );

        $childCluster = $this->createMock(Cluster::class);
        $catalog = new ClusterCatalog(
            ['ChildCluster' => $childCluster],
            [],
            $parentCatalog,
        );

        $result = iterator_to_array($catalog);
        $this->assertArrayHasKey('ChildCluster', $result);
        $this->assertArrayHasKey('ParentCluster', $result);
        $this->assertSame($childCluster, $result['ChildCluster']);
        $this->assertSame($parentCluster, $result['ParentCluster']);
    }

    public function testHasParentCatalogTrue(): void
    {
        $parentCatalog = new ClusterCatalog(
            [],
            [],
        );

        $catalog = new ClusterCatalog(
            [],
            [],
            $parentCatalog,
        );

        $this->assertTrue($catalog->hasParentCatalog());
    }

    public function testHasParentCatalogFalse(): void
    {
        $catalog = new ClusterCatalog(
            [],
            [],
        );

        $this->assertFalse($catalog->hasParentCatalog());
    }
}
