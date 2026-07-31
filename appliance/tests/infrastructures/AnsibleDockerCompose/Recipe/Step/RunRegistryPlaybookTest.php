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
use Teknoo\East\Paas\Infrastructures\DockerCompose\Contracts\RunnerFactoryInterface;
use Teknoo\East\Paas\Infrastructures\DockerCompose\Contracts\RunnerInterface;
use Teknoo\Space\Infrastructures\AnsibleDockerCompose\Recipe\Step\RunRegistryPlaybook;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\ConfigClusterInterface;
use Teknoo\Space\Object\Config\DockerComposeCluster;
use Teknoo\Space\Object\Config\Exception\UnsupportedClusterTypeException;

/**
 * Class RunRegistryPlaybookTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(RunRegistryPlaybook::class)]
class RunRegistryPlaybookTest extends TestCase
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

    public function testInvokeObtainsRunnerFromFactoryAndRunsThePlaybook(): void
    {
        $runner = $this->createMock(RunnerInterface::class);
        $runner->expects($this->once())
            ->method('run')
            ->with(
                '/path/to/registry.yml',
                '/tmp/inventory.ini',
                ['registry_container' => 'acct-registry'],
                $this->anything(),
                $this->anything(),
            )
            ->willReturnSelf();

        $factory = $this->createMock(RunnerFactoryInterface::class);
        $factory->expects($this->once())
            ->method('__invoke')
            ->with('ssh://deployer@host.example.com:22', $this->anything())
            ->willReturn($runner);

        $step = new RunRegistryPlaybook($factory, '/path/to/registry.yml');

        $result = $step(
            manager: $this->createStub(ManagerInterface::class),
            clusterCatalog: $this->dockerComposeCatalog(),
            inventoryPath: '/tmp/inventory.ini',
            extraVars: ['registry_container' => 'acct-registry'],
        );

        $this->assertInstanceOf(RunRegistryPlaybook::class, $result);
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

        $step = new RunRegistryPlaybook(
            $this->createStub(RunnerFactoryInterface::class),
            '/path/to/registry.yml',
        );

        $step(
            manager: $this->createStub(ManagerInterface::class),
            clusterCatalog: new ClusterCatalog(['k8s' => $cluster], []),
            inventoryPath: '/tmp/inventory.ini',
            extraVars: [],
        );
    }
}
