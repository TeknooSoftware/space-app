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

use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Infrastructures\AnsibleDockerCompose\Recipe\Step\BuildRegistryInventory;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\ConfigClusterInterface;
use Teknoo\Space\Object\Config\DockerComposeCluster;
use Teknoo\Space\Object\Config\Exception\UnsupportedClusterTypeException;

use function file_get_contents;
use function sys_get_temp_dir;
use function unlink;

/**
 * Class BuildRegistryInventoryTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(BuildRegistryInventory::class)]
class BuildRegistryInventoryTest extends TestCase
{
    private function dockerComposeCatalog(): ClusterCatalog
    {
        $cluster = new DockerComposeCluster(
            name: 'dc',
            sluggyName: 'dc',
            type: 'docker-compose',
            masterAddress: 'ssh://deployer@host.example.com:2222',
            dashboardAddress: '',
            isExternal: false,
            clientKey: '-----BEGIN OPENSSH PRIVATE KEY-----KEY',
            username: 'deployer',
            caCertificate: 'known-hosts',
            supportRegistry: true,
        );

        return new ClusterCatalog(['dc' => $cluster], []);
    }

    public function testInvokeWritesSingleHostInventory(): void
    {
        $captured = null;
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with($this->callback(function (array $workPlan) use (&$captured): bool {
                $captured = $workPlan['inventoryPath'] ?? null;

                return true;
            }))
            ->willReturnSelf();

        $filesystem = new Filesystem(new LocalFilesystemAdapter(sys_get_temp_dir()));
        $step = new BuildRegistryInventory($filesystem, sys_get_temp_dir());
        $result = $step($manager, $this->dockerComposeCatalog());

        $this->assertInstanceOf(BuildRegistryInventory::class, $result);
        $this->assertIsString($captured);
        $this->assertFileExists($captured);

        $content = (string) file_get_contents($captured);
        $this->assertStringContainsString('[registry_host]', $content);
        $this->assertStringContainsString('host.example.com', $content);
        $this->assertStringContainsString('ansible_port=2222', $content);
        $this->assertStringContainsString('ansible_user=deployer', $content);

        unlink($captured);
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

        $step = new BuildRegistryInventory(
            new Filesystem(new InMemoryFilesystemAdapter()),
            sys_get_temp_dir(),
        );

        $step(
            $this->createStub(ManagerInterface::class),
            new ClusterCatalog(['k8s' => $cluster], []),
        );
    }
}
