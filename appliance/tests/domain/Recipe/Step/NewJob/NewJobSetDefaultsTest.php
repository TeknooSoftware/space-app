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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\NewJob;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Paas\Object\Cluster as ClusterObject;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Kubernetes\Client;
use Teknoo\Space\Object\Config\Cluster;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\NewJob;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Recipe\Step\NewJob\NewJobSetDefaults;

/**
 * Class JobSetDefaultsTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(NewJobSetDefaults::class)]
class NewJobSetDefaultsTest extends TestCase
{
    private NewJobSetDefaults $newJobSetDefaults;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->newJobSetDefaults = new NewJobSetDefaults();
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            NewJobSetDefaults::class,
            ($this->newJobSetDefaults)(
                project: new SpaceProject($this->createMock(Project::class)),
                newJob: $this->createMock(NewJob::class),
                clusterCatalog: $this->createMock(ClusterCatalog::class),
            ),
        );
    }

    public function testInvokeWithLockedClusters(): void
    {
        $cluster1 = $this->createMock(ClusterObject::class);
        $cluster1->expects($this->once())
            ->method('isLocked')
            ->willReturn(true);

        $cluster2 = $this->createMock(ClusterObject::class);
        $cluster2->expects($this->once())
            ->method('isLocked')
            ->willReturn(true);

        $kubeClient = $this->createMock(Client::class);

        $config1 = new Cluster(
            name: 'cluster1',
            sluggyName: 'cluster1-slug',
            type: 'kubernetes',
            masterAddress: 'https://cluster1.example.com',
            storageProvisioner: 'provisioner1',
            dashboardAddress: 'https://dashboard1.example.com',
            kubernetesClient: $kubeClient,
            token: 'token1',
            supportRegistry: false,
            useHnc: false,
            isExternal: false,
        );

        $config2 = new Cluster(
            name: 'cluster2',
            sluggyName: 'cluster2-slug',
            type: 'kubernetes',
            masterAddress: 'https://cluster2.example.com',
            storageProvisioner: 'provisioner2',
            dashboardAddress: 'https://dashboard2.example.com',
            kubernetesClient: $kubeClient,
            token: 'token2',
            supportRegistry: false,
            useHnc: false,
            isExternal: false,
        );

        $clusterCatalog = $this->createMock(ClusterCatalog::class);
        $clusterCatalog->expects($this->exactly(2))
            ->method('getCluster')
            ->willReturnCallback(function ($cluster) use ($cluster1, $config1, $config2) {
                if ($cluster === $cluster1) {
                    return $config1;
                }
                return $config2;
            });

        $project = $this->createMock(Project::class);
        $project->expects($this->once())
            ->method('visit')
            ->willReturnCallback(function ($field, $callback) use ($cluster1, $cluster2, $project) {
                if ('clusters' === $field) {
                    $callback([$cluster1, $cluster2]);
                }
                return $project;
            });

        $spaceProject = new SpaceProject($project);
        $newJob = new NewJob();

        $result = ($this->newJobSetDefaults)(
            project: $spaceProject,
            newJob: $newJob,
            clusterCatalog: $clusterCatalog,
        );

        $this->assertInstanceOf(NewJobSetDefaults::class, $result);
        $this->assertArrayHasKey('cluster1', $newJob->storageProvisionerPerCluster);
        $this->assertArrayHasKey('cluster2', $newJob->storageProvisionerPerCluster);
        $this->assertEquals('provisioner1', $newJob->storageProvisionerPerCluster['cluster1']);
        $this->assertEquals('provisioner2', $newJob->storageProvisionerPerCluster['cluster2']);
    }

    public function testInvokeWithUnlockedClusters(): void
    {
        $cluster1 = $this->createMock(ClusterObject::class);
        $cluster1->expects($this->once())
            ->method('isLocked')
            ->willReturn(false);

        $clusterCatalog = $this->createMock(ClusterCatalog::class);
        $clusterCatalog->expects($this->never())
            ->method('getCluster');

        $project = $this->createMock(Project::class);
        $project->expects($this->once())
            ->method('visit')
            ->willReturnCallback(function ($field, $callback) use ($cluster1, $project) {
                if ('clusters' === $field) {
                    $callback([$cluster1]);
                }
                return $project;
            });

        $spaceProject = new SpaceProject($project);
        $newJob = new NewJob();

        $result = ($this->newJobSetDefaults)(
            project: $spaceProject,
            newJob: $newJob,
            clusterCatalog: $clusterCatalog,
        );

        $this->assertInstanceOf(NewJobSetDefaults::class, $result);
        $this->assertEmpty($newJob->storageProvisionerPerCluster);
    }

    public function testInvokeWithMixedClusters(): void
    {
        $lockedCluster = $this->createMock(ClusterObject::class);
        $lockedCluster->expects($this->once())
            ->method('isLocked')
            ->willReturn(true);

        $unlockedCluster = $this->createMock(ClusterObject::class);
        $unlockedCluster->expects($this->once())
            ->method('isLocked')
            ->willReturn(false);

        $kubeClient = $this->createMock(Client::class);

        $config = new Cluster(
            name: 'locked-cluster',
            sluggyName: 'locked-cluster-slug',
            type: 'kubernetes',
            masterAddress: 'https://locked.example.com',
            storageProvisioner: 'locked-provisioner',
            dashboardAddress: 'https://dashboard-locked.example.com',
            kubernetesClient: $kubeClient,
            token: 'token-locked',
            supportRegistry: false,
            useHnc: false,
            isExternal: false,
        );

        $clusterCatalog = $this->createMock(ClusterCatalog::class);
        $clusterCatalog->expects($this->once())
            ->method('getCluster')
            ->with($lockedCluster)
            ->willReturn($config);

        $project = $this->createMock(Project::class);
        $project->expects($this->once())
            ->method('visit')
            ->willReturnCallback(function ($field, $callback) use ($lockedCluster, $unlockedCluster, $project) {
                if ('clusters' === $field) {
                    $callback([$lockedCluster, $unlockedCluster]);
                }
                return $project;
            });

        $spaceProject = new SpaceProject($project);
        $newJob = new NewJob();

        $result = ($this->newJobSetDefaults)(
            project: $spaceProject,
            newJob: $newJob,
            clusterCatalog: $clusterCatalog,
        );

        $this->assertInstanceOf(NewJobSetDefaults::class, $result);
        $this->assertArrayHasKey('locked-cluster', $newJob->storageProvisionerPerCluster);
        $this->assertEquals('locked-provisioner', $newJob->storageProvisionerPerCluster['locked-cluster']);
        $this->assertCount(1, $newJob->storageProvisionerPerCluster);
    }
}
