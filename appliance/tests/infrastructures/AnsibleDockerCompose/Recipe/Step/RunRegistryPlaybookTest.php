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
use RuntimeException;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\ClusterCredentials;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Infrastructures\AnsibleDockerCompose\PlaybookRunner;
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
    private function buildCluster(string $name, string $host): DockerComposeCluster
    {
        return new DockerComposeCluster(
            name: $name,
            sluggyName: $name,
            type: 'docker-compose',
            masterAddress: 'ssh://deployer@' . $host . ':22',
            dashboardAddress: '',
            isExternal: false,
            clientKey: '-----BEGIN OPENSSH PRIVATE KEY-----KEY',
            username: 'deployer',
            caCertificate: 'known-hosts',
            supportRegistry: true,
        );
    }

    private function dockerComposeCatalog(): ClusterCatalog
    {
        return new ClusterCatalog(['dc' => $this->buildCluster('dc', 'host.example.com')], []);
    }

    /**
     * @param callable(PromiseInterface<array<string, mixed>|string, mixed>): mixed $resolver
     */
    private function buildPlaybookRunnerResolving(callable $resolver): PlaybookRunner
    {
        $playbookRunner = $this->createMock(PlaybookRunner::class);
        $playbookRunner->expects($this->once())
            ->method('run')
            ->willReturnCallback(
                static function (
                    string $playbookPath,
                    string $address,
                    ClusterCredentials $credentials,
                    array $extraVars,
                    PromiseInterface $promise,
                ) use (
                    $playbookRunner,
                    $resolver,
                ): PlaybookRunner {
                    $resolver($promise);

                    return $playbookRunner;
                }
            );

        return $playbookRunner;
    }

    public function testInvokeRunsThePlaybookOnTheRegistryHost(): void
    {
        $playbookRunner = $this->createMock(PlaybookRunner::class);
        $playbookRunner->expects($this->once())
            ->method('run')
            ->with(
                '/path/to/registry.yml',
                'ssh://deployer@host.example.com:22',
                $this->callback(
                    static fn (ClusterCredentials $credentials): bool => 'deployer' === $credentials->getUsername()
                        && '-----BEGIN OPENSSH PRIVATE KEY-----KEY' === $credentials->getClientKey(),
                ),
                ['registry_container' => 'acct-registry'],
                $this->isInstanceOf(PromiseInterface::class),
            )
            ->willReturnSelf();

        $step = new RunRegistryPlaybook($playbookRunner, '/path/to/registry.yml');

        $result = $step(
            manager: $this->createStub(ManagerInterface::class),
            clusterCatalog: $this->dockerComposeCatalog(),
            extraVars: ['registry_container' => 'acct-registry'],
        );

        $this->assertInstanceOf(RunRegistryPlaybook::class, $result);
    }

    /**
     * The playbook runs over SSH on the registry host: targeting the first Docker host of the catalog instead of
     * the cluster recorded in the account registry would provision the registry on the wrong machine.
     */
    public function testInvokeUsesTheResolvedRegistryClusterInsteadOfTheFirstOne(): void
    {
        $catalog = new ClusterCatalog(
            [
                'first' => $this->buildCluster('first', 'first.example.com'),
                'recorded' => $this->buildCluster('recorded', 'recorded.example.com'),
            ],
            [],
        );

        $playbookRunner = $this->createMock(PlaybookRunner::class);
        $playbookRunner->expects($this->once())
            ->method('run')
            ->with(
                '/path/to/registry.yml',
                'ssh://deployer@recorded.example.com:22',
                $this->anything(),
                [],
                $this->anything(),
            )
            ->willReturnSelf();

        $step = new RunRegistryPlaybook($playbookRunner, '/path/to/registry.yml');

        $result = $step(
            manager: $this->createStub(ManagerInterface::class),
            clusterCatalog: $catalog,
            extraVars: [],
            registryClusterName: 'recorded',
        );

        $this->assertInstanceOf(RunRegistryPlaybook::class, $result);
    }

    public function testInvokeStoresTheResultInTheWorkPlanOnSuccess(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(['registryInstallResult' => ['changed' => 1]])
            ->willReturnSelf();
        $manager->expects($this->never())
            ->method('error');

        $step = new RunRegistryPlaybook(
            $this->buildPlaybookRunnerResolving(
                static fn (PromiseInterface $promise): PromiseInterface => $promise->success(['changed' => 1]),
            ),
            '/path/to/registry.yml',
        );

        $this->assertInstanceOf(
            RunRegistryPlaybook::class,
            $step(
                manager: $manager,
                clusterCatalog: $this->dockerComposeCatalog(),
                extraVars: [],
            ),
        );
    }

    public function testInvokeReportsTheErrorToTheManagerOnFailure(): void
    {
        $error = new RuntimeException('playbook failed');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('updateWorkPlan');
        $manager->expects($this->once())
            ->method('error')
            ->with($error)
            ->willReturnSelf();

        $step = new RunRegistryPlaybook(
            $this->buildPlaybookRunnerResolving(
                static fn (PromiseInterface $promise): PromiseInterface => $promise->fail($error),
            ),
            '/path/to/registry.yml',
        );

        $this->assertInstanceOf(
            RunRegistryPlaybook::class,
            $step(
                manager: $manager,
                clusterCatalog: $this->dockerComposeCatalog(),
                extraVars: [],
            ),
        );
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

        $playbookRunner = $this->createMock(PlaybookRunner::class);
        $playbookRunner->expects($this->never())->method('run');

        $this->expectException(UnsupportedClusterTypeException::class);

        $step = new RunRegistryPlaybook($playbookRunner, '/path/to/registry.yml');

        $step(
            manager: $this->createStub(ManagerInterface::class),
            clusterCatalog: new ClusterCatalog(['k8s' => $cluster], []),
            extraVars: [],
        );
    }
}
