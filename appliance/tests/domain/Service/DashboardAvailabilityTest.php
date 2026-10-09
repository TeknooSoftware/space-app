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

namespace Teknoo\Space\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Kubernetes\Client;
use Teknoo\Space\Object\Config\DockerComposeCluster;
use Teknoo\Space\Object\Config\KubernetesCluster;
use Teknoo\Space\Service\DashboardAvailability;

/**
 * Class DashboardAvailabilityTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(DashboardAvailability::class)]
class DashboardAvailabilityTest extends TestCase
{
    private function createKubernetesCluster(string $dashboardAddress, bool $isExternal): KubernetesCluster
    {
        return new KubernetesCluster(
            name: 'foo',
            sluggyName: 'foo',
            type: 'kubernetes',
            masterAddress: 'https://kubernetes.test',
            storageProvisioner: 'foo',
            dashboardAddress: $dashboardAddress,
            kubernetesClient: $this->createStub(Client::class),
            token: 'foo',
            supportRegistry: false,
            useHnc: false,
            isExternal: $isExternal,
        );
    }

    public function testAKubernetesClusterWithADashboardIsAvailable(): void
    {
        $this->assertTrue(
            new DashboardAvailability()->isAvailable(
                $this->createKubernetesCluster('https://dashboard.test/', false),
            ),
        );
    }

    public function testAKubernetesClusterWithoutDashboardIsNotAvailable(): void
    {
        $this->assertFalse(
            new DashboardAvailability()->isAvailable($this->createKubernetesCluster(' ', false)),
        );
    }

    public function testTheDashboardOfAnExternalClusterIsNotAvailableByDefault(): void
    {
        $this->assertFalse(
            new DashboardAvailability()->isAvailable(
                $this->createKubernetesCluster('https://dashboard.client.test/', true),
            ),
        );
    }

    public function testTheDashboardOfAnExternalClusterIsAvailableWhenAllowed(): void
    {
        $availability = new DashboardAvailability(externalDashboardsEnabled: true);

        $this->assertTrue(
            $availability->isAvailable($this->createKubernetesCluster('https://dashboard.client.test/', true)),
        );
        $this->assertFalse($availability->isAvailable($this->createKubernetesCluster('', true)));
    }

    public function testADockerComposeClusterIsNotAvailable(): void
    {
        $this->assertFalse(
            new DashboardAvailability()->isAvailable(
                new DockerComposeCluster(
                    name: 'foo',
                    sluggyName: 'foo',
                    type: 'docker-compose',
                    masterAddress: 'ssh://u@h:22',
                    dashboardAddress: 'https://dashboard.test/',
                    isExternal: false,
                    clientKey: 'k',
                ),
            ),
        );
    }
}
