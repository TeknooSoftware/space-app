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

namespace Teknoo\Space\Tests\Unit\Infrastructures\AnsibleDockerCompose\Recipe\Step;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Infrastructures\AnsibleDockerCompose\Recipe\Step\PersistSshIdentity;
use Teknoo\Space\Object\Config\DockerComposeCluster;
use Teknoo\Space\Object\Config\Exception\UnsupportedClusterTypeException;
use Teknoo\Space\Object\Config\KubernetesCluster;

/**
 * Class PersistSshIdentityTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(PersistSshIdentity::class)]
class PersistSshIdentityTest extends TestCase
{
    private PersistSshIdentity $step;

    protected function setUp(): void
    {
        parent::setUp();

        $this->step = new PersistSshIdentity();
    }

    public function testInvokeStagesSshIdentityForDockerCompose(): void
    {
        $cluster = new DockerComposeCluster(
            name: 'dc',
            sluggyName: 'dc',
            type: 'docker-compose',
            masterAddress: 'ssh://deployer@host:22',
            dashboardAddress: '',
            isExternal: false,
            clientKey: '-----BEGIN OPENSSH PRIVATE KEY-----KEY',
            caCertificate: 'host ssh-ed25519 AAAA',
        );

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with($this->callback(
                static fn (array $workPlan): bool => 'acct-prod' === $workPlan['kubeNamespace']
                    && '-----BEGIN OPENSSH PRIVATE KEY-----KEY' === $workPlan['clientKey']
                    && 'host ssh-ed25519 AAAA' === $workPlan['caCertificate']
                    && '' === $workPlan['token']
                    && '' === $workPlan['serviceName']
                    && '' === $workPlan['roleName']
                    && '' === $workPlan['roleBindingName']
            ))
            ->willReturnSelf();

        $result = ($this->step)(
            manager: $manager,
            clusterConfig: $cluster,
            accountNamespace: 'acct',
            envName: 'prod',
        );

        $this->assertInstanceOf(PersistSshIdentity::class, $result);
    }

    public function testInvokeThrowsOnNonDockerComposeCluster(): void
    {
        $this->expectException(UnsupportedClusterTypeException::class);

        ($this->step)(
            manager: $this->createStub(ManagerInterface::class),
            clusterConfig: $this->createStub(KubernetesCluster::class),
            accountNamespace: 'acct',
            envName: 'prod',
        );
    }
}
