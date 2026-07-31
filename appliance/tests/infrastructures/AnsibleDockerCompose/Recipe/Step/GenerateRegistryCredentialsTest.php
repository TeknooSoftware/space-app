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
use Teknoo\Space\Infrastructures\AnsibleDockerCompose\Recipe\Step\GenerateRegistryCredentials;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\ConfigClusterInterface;
use Teknoo\Space\Object\Config\DockerComposeCluster;
use Teknoo\Space\Object\Config\Exception\UnsupportedClusterTypeException;

use function str_starts_with;

/**
 * Class GenerateRegistryCredentialsTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(GenerateRegistryCredentials::class)]
class GenerateRegistryCredentialsTest extends TestCase
{
    private function dockerComposeCatalog(): ClusterCatalog
    {
        $cluster = new DockerComposeCluster(
            name: 'dc',
            sluggyName: 'dc',
            type: 'docker-compose',
            masterAddress: 'ssh://deployer@host.example.com:22',
            dashboardAddress: '',
            isExternal: false,
            clientKey: '-----BEGIN OPENSSH PRIVATE KEY-----KEY',
            username: 'deployer',
            caCertificate: 'known-hosts',
            supportRegistry: true,
        );

        return new ClusterCatalog(['dc' => $cluster], []);
    }

    private function buildStep(): GenerateRegistryCredentials
    {
        return new GenerateRegistryCredentials(
            registryImage: 'registry:2',
            registryNetwork: 'space-registry',
            registryPort: 5000,
            registryTls: false,
            deployRoot: '/opt/paas',
        );
    }

    public function testInvokeStagesCredentialsAndExtraVars(): void
    {
        $captured = null;
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with($this->callback(function (array $workPlan) use (&$captured): bool {
                $captured = $workPlan;

                return true;
            }))
            ->willReturnSelf();

        $result = ($this->buildStep())($manager, $this->dockerComposeCatalog(), 'acct');

        $this->assertInstanceOf(GenerateRegistryCredentials::class, $result);
        $this->assertSame('acct-registry:5000', $captured['registryUrl']);
        $this->assertSame('acct', $captured['registryAccountName']);
        $this->assertSame('acct-docker-config', $captured['registryConfigName']);
        $this->assertSame('acct', $captured['kubeNamespace']);
        $this->assertSame('acct-registry-data', $captured['persistentVolumeClaimName']);
        $this->assertNotEmpty($captured['registryPassword']);

        $extraVars = $captured['extraVars'];
        $this->assertSame('acct-registry', $extraVars['registry_container']);
        $this->assertSame('registry:2', $extraVars['registry_image']);
        $this->assertSame('space-registry', $extraVars['registry_network']);
        $this->assertSame(5000, $extraVars['registry_port']);
        $this->assertFalse($extraVars['registry_tls']);
        $this->assertSame('acct-registry-data', $extraVars['registry_volume']);
        $this->assertSame('/opt/paas', $extraVars['deploy_root']);
        $this->assertTrue(str_starts_with((string) $extraVars['registry_htpasswd'], 'acct:'));
    }

    public function testInvokeThrowsOnNonDockerComposeRegistryCluster(): void
    {
        $cluster = new class implements ConfigClusterInterface {
            public string $name = 'k8s';

            public string $sluggyName = 'k8s';

            public string $type = 'kubernetes';

            public string $masterAddress = 'https://k8s.example.com';

            public string $dashboardAddress = '';

            public bool $supportRegistry = true;

            public bool $useHnc = false;

            public bool $isExternal = false;
        };

        $this->expectException(UnsupportedClusterTypeException::class);

        ($this->buildStep())(
            $this->createStub(ManagerInterface::class),
            new ClusterCatalog(['k8s' => $cluster], []),
            'acct',
        );
    }
}
