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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Job;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Cluster;
use Teknoo\East\Paas\Object\Job;
use Teknoo\Space\Object\DTO\NewJob;
use Teknoo\Space\Object\Persisted\AccountRegistry;
use Teknoo\Space\Recipe\Step\Job\JobSetDefaults;

/**
 * Class JobSetDefaultsTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(JobSetDefaults::class)]
class JobSetDefaultsTest extends TestCase
{
    private JobSetDefaults $jobSetDefaults;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobSetDefaults = new JobSetDefaults();
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            JobSetDefaults::class,
            ($this->jobSetDefaults)(
                manager: $this->createStub(ManagerInterface::class),
                job: $this->createStub(Job::class),
                accountRegistry: $this->createStub(AccountRegistry::class),
                newJob: $this->createStub(NewJob::class),
            ),
        );
    }

    public function testInvokeWithBasicDefaults(): void
    {
        $accountRegistry = $this->createMock(AccountRegistry::class);
        $accountRegistry->expects($this->once())
            ->method('getRegistryConfigName')
            ->willReturn('my-registry-config');

        $job = $this->createMock(Job::class);
        $job->expects($this->once())
            ->method('visit')
            ->with('clusters', $this->isCallable())
            ->willReturnCallback(function ($key, $callback) use ($job) {
                $callback([]);
                return $job;
            });
        $job->expects($this->once())
            ->method('setDefaults')
            ->with(
                $this->callback(function ($defaults) {
                    return isset($defaults['oci-registry-config-name'])
                        && 'my-registry-config' === $defaults['oci-registry-config-name']
                        && !isset($defaults['clusters']);
                })
            );

        $result = ($this->jobSetDefaults)(
            manager: $this->createStub(ManagerInterface::class),
            job: $job,
            accountRegistry: $accountRegistry,
            newJob: $this->createStub(NewJob::class),
        );

        $this->assertInstanceOf(JobSetDefaults::class, $result);
    }

    public function testInvokeWithLockedClusterAndStorageProvisioner(): void
    {
        $accountRegistry = $this->createMock(AccountRegistry::class);
        $accountRegistry->expects($this->once())
            ->method('getRegistryConfigName')
            ->willReturn('my-registry-config');

        $cluster = $this->createMock(Cluster::class);
        $cluster->expects($this->once())
            ->method('isLocked')
            ->willReturn(true);
        $cluster->expects($this->once())
            ->method('__toString')
            ->willReturn('cluster-1');

        $newJob = $this->createStub(NewJob::class);
        $newJob->storageProvisionerPerCluster = [
            'cluster-1' => 'provisioner-1',
        ];

        $job = $this->createMock(Job::class);
        $job->expects($this->once())
            ->method('visit')
            ->with('clusters', $this->isCallable())
            ->willReturnCallback(function ($key, $callback) use ($job, $cluster) {
                $callback([$cluster]);
                return $job;
            });
        $job->expects($this->once())
            ->method('setDefaults')
            ->with(
                $this->callback(function ($defaults) {
                    return isset($defaults['oci-registry-config-name'])
                        && 'my-registry-config' === $defaults['oci-registry-config-name']
                        && isset($defaults['clusters']['cluster-1']['storage-provider'])
                        && 'provisioner-1' === $defaults['clusters']['cluster-1']['storage-provider'];
                })
            );

        $result = ($this->jobSetDefaults)(
            manager: $this->createStub(ManagerInterface::class),
            job: $job,
            accountRegistry: $accountRegistry,
            newJob: $newJob,
        );

        $this->assertInstanceOf(JobSetDefaults::class, $result);
    }

    public function testInvokeWithLockedClusterWithoutStorageProvisioner(): void
    {
        $accountRegistry = $this->createMock(AccountRegistry::class);
        $accountRegistry->expects($this->once())
            ->method('getRegistryConfigName')
            ->willReturn('my-registry-config');

        $cluster = $this->createMock(Cluster::class);
        $cluster->expects($this->once())
            ->method('isLocked')
            ->willReturn(true);
        $cluster->expects($this->once())
            ->method('__toString')
            ->willReturn('cluster-2');

        $newJob = $this->createStub(NewJob::class);
        $newJob->storageProvisionerPerCluster = [];

        $job = $this->createMock(Job::class);
        $job->expects($this->once())
            ->method('visit')
            ->with('clusters', $this->isCallable())
            ->willReturnCallback(function ($key, $callback) use ($job, $cluster) {
                $callback([$cluster]);
                return $job;
            });
        $job->expects($this->once())
            ->method('setDefaults')
            ->with(
                $this->callback(function ($defaults) {
                    return isset($defaults['oci-registry-config-name'])
                        && 'my-registry-config' === $defaults['oci-registry-config-name']
                        && !isset($defaults['clusters']);
                })
            );

        $result = ($this->jobSetDefaults)(
            manager: $this->createStub(ManagerInterface::class),
            job: $job,
            accountRegistry: $accountRegistry,
            newJob: $newJob,
        );

        $this->assertInstanceOf(JobSetDefaults::class, $result);
    }

    public function testInvokeWithUnlockedCluster(): void
    {
        $accountRegistry = $this->createMock(AccountRegistry::class);
        $accountRegistry->expects($this->once())
            ->method('getRegistryConfigName')
            ->willReturn('my-registry-config');

        $cluster = $this->createMock(Cluster::class);
        $cluster->expects($this->once())
            ->method('isLocked')
            ->willReturn(false);
        $cluster->expects($this->never())
            ->method('__toString');

        $newJob = $this->createStub(NewJob::class);
        $newJob->storageProvisionerPerCluster = [
            'cluster-3' => 'provisioner-3',
        ];

        $job = $this->createMock(Job::class);
        $job->expects($this->once())
            ->method('visit')
            ->with('clusters', $this->isCallable())
            ->willReturnCallback(function ($key, $callback) use ($job, $cluster) {
                $callback([$cluster]);
                return $job;
            });
        $job->expects($this->once())
            ->method('setDefaults')
            ->with(
                $this->callback(function ($defaults) {
                    return isset($defaults['oci-registry-config-name'])
                        && 'my-registry-config' === $defaults['oci-registry-config-name']
                        && !isset($defaults['clusters']);
                })
            );

        $result = ($this->jobSetDefaults)(
            manager: $this->createStub(ManagerInterface::class),
            job: $job,
            accountRegistry: $accountRegistry,
            newJob: $newJob,
        );

        $this->assertInstanceOf(JobSetDefaults::class, $result);
    }

    public function testInvokeWithMultipleClusters(): void
    {
        $accountRegistry = $this->createMock(AccountRegistry::class);
        $accountRegistry->expects($this->once())
            ->method('getRegistryConfigName')
            ->willReturn('my-registry-config');

        $cluster1 = $this->createMock(Cluster::class);
        $cluster1->expects($this->once())
            ->method('isLocked')
            ->willReturn(true);
        $cluster1->expects($this->once())
            ->method('__toString')
            ->willReturn('cluster-1');

        $cluster2 = $this->createMock(Cluster::class);
        $cluster2->expects($this->once())
            ->method('isLocked')
            ->willReturn(true);
        $cluster2->expects($this->once())
            ->method('__toString')
            ->willReturn('cluster-2');

        $newJob = $this->createStub(NewJob::class);
        $newJob->storageProvisionerPerCluster = [
            'cluster-1' => 'provisioner-1',
            'cluster-2' => 'provisioner-2',
        ];

        $job = $this->createMock(Job::class);
        $job->expects($this->once())
            ->method('visit')
            ->with('clusters', $this->isCallable())
            ->willReturnCallback(function ($key, $callback) use ($job, $cluster1, $cluster2) {
                $callback([$cluster1, $cluster2]);
                return $job;
            });
        $job->expects($this->once())
            ->method('setDefaults')
            ->with(
                $this->callback(function ($defaults) {
                    return isset($defaults['oci-registry-config-name'])
                        && 'my-registry-config' === $defaults['oci-registry-config-name']
                        && isset($defaults['clusters']['cluster-1']['storage-provider'])
                        && 'provisioner-1' === $defaults['clusters']['cluster-1']['storage-provider']
                        && isset($defaults['clusters']['cluster-2']['storage-provider'])
                        && 'provisioner-2' === $defaults['clusters']['cluster-2']['storage-provider'];
                })
            );

        $result = ($this->jobSetDefaults)(
            manager: $this->createStub(ManagerInterface::class),
            job: $job,
            accountRegistry: $accountRegistry,
            newJob: $newJob,
        );

        $this->assertInstanceOf(JobSetDefaults::class, $result);
    }

    public function testInvokeWithNonClusterObject(): void
    {
        $accountRegistry = $this->createMock(AccountRegistry::class);
        $accountRegistry->expects($this->once())
            ->method('getRegistryConfigName')
            ->willReturn('my-registry-config');

        $nonClusterObject = new \stdClass();

        $job = $this->createMock(Job::class);
        $job->expects($this->once())
            ->method('visit')
            ->with('clusters', $this->isCallable())
            ->willReturnCallback(function ($key, $callback) use ($job, $nonClusterObject) {
                $callback([$nonClusterObject]);
                return $job;
            });
        $job->expects($this->once())
            ->method('setDefaults')
            ->with(
                $this->callback(function ($defaults) {
                    return isset($defaults['oci-registry-config-name'])
                        && 'my-registry-config' === $defaults['oci-registry-config-name']
                        && !isset($defaults['clusters']);
                })
            );

        $result = ($this->jobSetDefaults)(
            manager: $this->createStub(ManagerInterface::class),
            job: $job,
            accountRegistry: $accountRegistry,
            newJob: $this->createStub(NewJob::class),
        );

        $this->assertInstanceOf(JobSetDefaults::class, $result);
    }

    public function testInvokeWithMixedClusterTypes(): void
    {
        $accountRegistry = $this->createMock(AccountRegistry::class);
        $accountRegistry->expects($this->once())
            ->method('getRegistryConfigName')
            ->willReturn('my-registry-config');

        $validCluster = $this->createMock(Cluster::class);
        $validCluster->expects($this->once())
            ->method('isLocked')
            ->willReturn(true);
        $validCluster->expects($this->once())
            ->method('__toString')
            ->willReturn('cluster-valid');

        $nonClusterObject = new \stdClass();

        $newJob = $this->createStub(NewJob::class);
        $newJob->storageProvisionerPerCluster = [
            'cluster-valid' => 'provisioner-valid',
        ];

        $job = $this->createMock(Job::class);
        $job->expects($this->once())
            ->method('visit')
            ->with('clusters', $this->isCallable())
            ->willReturnCallback(function ($key, $callback) use ($job, $validCluster, $nonClusterObject) {
                $callback([$nonClusterObject, $validCluster]);
                return $job;
            });
        $job->expects($this->once())
            ->method('setDefaults')
            ->with(
                $this->callback(function ($defaults) {
                    return isset($defaults['oci-registry-config-name'])
                        && 'my-registry-config' === $defaults['oci-registry-config-name']
                        && isset($defaults['clusters']['cluster-valid']['storage-provider'])
                        && 'provisioner-valid' === $defaults['clusters']['cluster-valid']['storage-provider']
                        && 1 === count($defaults['clusters']);
                })
            );

        $result = ($this->jobSetDefaults)(
            manager: $this->createStub(ManagerInterface::class),
            job: $job,
            accountRegistry: $accountRegistry,
            newJob: $newJob,
        );

        $this->assertInstanceOf(JobSetDefaults::class, $result);
    }
}
