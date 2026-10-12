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

namespace Teknoo\Space\Tests\Unit\Object\DTO;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Kubernetes\Client;
use Teknoo\Space\Object\Config\DashboardProfile;
use Teknoo\Space\Object\Config\DockerComposeCluster;
use Teknoo\Space\Object\Config\KubernetesCluster;
use Teknoo\Space\Object\DTO\DashboardTarget;

/**
 * Class DashboardTargetTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(DashboardTarget::class)]
class DashboardTargetTest extends TestCase
{
    private function createCluster(): KubernetesCluster
    {
        return new KubernetesCluster(
            name: 'Cluster Name',
            sluggyName: 'cluster-name',
            type: 'kubernetes',
            masterAddress: 'https://kubernetes.test',
            storageProvisioner: 'foo',
            dashboardAddress: 'https://dashboard.test/__headlamp/',
            kubernetesClient: $this->createStub(Client::class),
            token: 'cluster-token',
            supportRegistry: false,
            useHnc: false,
            isExternal: false,
        );
    }

    public function testConstructForANamespace(): void
    {
        $cluster = $this->createCluster();
        $profile = new DashboardProfile(name: 'headlamp', requestHeaders: []);

        $target = new DashboardTarget(
            cluster: $cluster,
            profile: $profile,
            token: 'env-token',
            clusterName: 'cluster-name',
            envName: 'prod',
            namespace: 'space-ns',
        );

        $this->assertSame($cluster, $target->cluster);
        $this->assertSame($profile, $target->profile);
        $this->assertSame('env-token', $target->token);
        $this->assertSame('cluster-name', $target->clusterName);
        $this->assertSame('prod', $target->envName);
        $this->assertSame('space-ns', $target->namespace);
    }

    public function testConstructForAllNamespaces(): void
    {
        $target = new DashboardTarget(
            cluster: $this->createCluster(),
            profile: new DashboardProfile(name: 'headlamp', requestHeaders: []),
            token: 'cluster-token',
            clusterName: 'cluster-name',
            envName: '_all',
        );

        $this->assertNull($target->namespace);
    }

    public function testConstructForAClusterOfAnyType(): void
    {
        $cluster = new DockerComposeCluster(
            name: 'Compose',
            sluggyName: 'compose',
            type: 'docker-compose',
            masterAddress: 'ssh://u@h:22',
            dashboardAddress: 'https://dashboard.test/',
            isExternal: false,
            dashboardType: 'headlamp',
        );

        $target = new DashboardTarget(
            cluster: $cluster,
            profile: new DashboardProfile(name: 'headlamp', requestHeaders: []),
            token: 'env-token',
            clusterName: 'compose',
            envName: 'prod',
        );

        $this->assertSame($cluster, $target->cluster);
    }
}
