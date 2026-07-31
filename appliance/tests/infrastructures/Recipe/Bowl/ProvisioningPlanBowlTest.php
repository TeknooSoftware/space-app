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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Recipe\Bowl;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Space\Cluster\Contract\ProvisioningPlanDirectoryInterface;
use Teknoo\Space\Infrastructures\Recipe\Bowl\ProvisioningPlanBowl;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\ConfigClusterInterface;
use Teknoo\Space\Object\Config\Exception\UnsupportedClusterTypeException;

/**
 * Class ProvisioningPlanBowlTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ProvisioningPlanBowl::class)]
class ProvisioningPlanBowlTest extends TestCase
{
    private function clusterOfType(string $type, bool $supportRegistry = false): ConfigClusterInterface
    {
        return new class ($type, $supportRegistry) implements ConfigClusterInterface {
            public string $name = 'foo';

            public string $sluggyName = 'foo';

            public string $masterAddress = '';

            public string $dashboardAddress = '';

            public bool $useHnc = false;

            public bool $isExternal = false;

            public function __construct(
                public string $type,
                public bool $supportRegistry,
            ) {
            }
        };
    }

    public function testExecuteResolvesTheEnvironmentInstallPlanByClusterType(): void
    {
        $plan = $this->createStub(EditablePlanInterface::class);

        $directory = $this->createMock(ProvisioningPlanDirectoryInterface::class);
        $directory->expects($this->once())
            ->method('environmentInstall')
            ->with('kubernetes')
            ->willReturn($plan);

        $bowl = new ProvisioningPlanBowl($directory, ProvisioningPlanBowl::ROLE_ENVIRONMENT_INSTALL, 0);

        $workPlan = [
            'clusterCatalog' => new ClusterCatalog(['foo' => $this->clusterOfType('kubernetes')], []),
            'clusterName' => 'foo',
        ];

        $result = $bowl->execute($this->createStub(ChefInterface::class), $workPlan);

        $this->assertInstanceOf(BowlInterface::class, $result);
    }

    public function testExecuteResolvesRegistryInstallForDockerCompose(): void
    {
        $plan = $this->createStub(EditablePlanInterface::class);

        $directory = $this->createMock(ProvisioningPlanDirectoryInterface::class);
        $directory->expects($this->once())
            ->method('registryInstall')
            ->with('docker-compose')
            ->willReturn($plan);

        $bowl = new ProvisioningPlanBowl($directory, ProvisioningPlanBowl::ROLE_REGISTRY_INSTALL, 0);

        $workPlan = [
            'clusterCatalog' => new ClusterCatalog(['foo' => $this->clusterOfType('docker-compose')], []),
            'clusterName' => 'foo',
        ];

        $bowl->execute($this->createStub(ChefInterface::class), $workPlan);
    }

    public function testExecuteFallsBackToRegistryClusterWhenNoClusterName(): void
    {
        $plan = $this->createStub(EditablePlanInterface::class);

        $directory = $this->createMock(ProvisioningPlanDirectoryInterface::class);
        $directory->expects($this->once())
            ->method('registryReinstall')
            ->with('docker-compose')
            ->willReturn($plan);

        $bowl = new ProvisioningPlanBowl($directory, ProvisioningPlanBowl::ROLE_REGISTRY_REINSTALL, 0);

        //No clusterName in the work plan: the bowl resolves the type from the account's registry cluster.
        $workPlan = [
            'clusterCatalog' => new ClusterCatalog(
                ['foo' => $this->clusterOfType('docker-compose', true)],
                [],
            ),
        ];

        $result = $bowl->execute($this->createStub(ChefInterface::class), $workPlan);

        $this->assertInstanceOf(BowlInterface::class, $result);
    }

    public function testExecuteThrowsWhenClusterContextIsMissing(): void
    {
        $bowl = new ProvisioningPlanBowl(
            $this->createStub(ProvisioningPlanDirectoryInterface::class),
            ProvisioningPlanBowl::ROLE_ENVIRONMENT_INSTALL,
            0,
        );

        $this->expectException(UnsupportedClusterTypeException::class);

        $workPlan = [];
        $bowl->execute($this->createStub(ChefInterface::class), $workPlan);
    }
}
