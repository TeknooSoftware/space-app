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

namespace Teknoo\Space\Tests\Unit\Cluster;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Space\Cluster\Contract\ProvisioningPlanDirectoryInterface;
use Teknoo\Space\Cluster\ProvisioningPlanDirectory;
use Teknoo\Space\Cluster\ProvisioningPlanSet;
use Teknoo\Space\Object\Config\Exception\UnsupportedClusterTypeException;

/**
 * Class ProvisioningPlanDirectoryTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ProvisioningPlanDirectory::class)]
#[CoversClass(ProvisioningPlanSet::class)]
class ProvisioningPlanDirectoryTest extends TestCase
{
    private function buildSet(): ProvisioningPlanSet
    {
        return new ProvisioningPlanSet(
            environmentInstall: $this->createStub(EditablePlanInterface::class),
            environmentReinstall: $this->createStub(EditablePlanInterface::class),
            refreshQuota: $this->createStub(EditablePlanInterface::class),
            registryInstall: $this->createStub(EditablePlanInterface::class),
            registryReinstall: $this->createStub(EditablePlanInterface::class),
        );
    }

    public function testItImplementsTheContract(): void
    {
        $this->assertInstanceOf(ProvisioningPlanDirectoryInterface::class, new ProvisioningPlanDirectory());
    }

    public function testRegisterIsFluent(): void
    {
        $directory = new ProvisioningPlanDirectory();

        $this->assertSame($directory, $directory->register('kubernetes', $this->buildSet()));
    }

    public function testItReturnsTheIdenticalRegisteredKubernetesPlans(): void
    {
        $set = $this->buildSet();
        $directory = (new ProvisioningPlanDirectory())->register('kubernetes', $set);

        $this->assertSame($set->environmentInstall, $directory->environmentInstall('kubernetes'));
        $this->assertSame($set->environmentReinstall, $directory->environmentReinstall('kubernetes'));
        $this->assertSame($set->refreshQuota, $directory->refreshQuota('kubernetes'));
        $this->assertSame($set->registryInstall, $directory->registryInstall('kubernetes'));
        $this->assertSame($set->registryReinstall, $directory->registryReinstall('kubernetes'));
    }

    public function testItResolvesADockerComposePlanSet(): void
    {
        $dcSet = $this->buildSet();
        $directory = (new ProvisioningPlanDirectory())
            ->register('kubernetes', $this->buildSet())
            ->register('docker-compose', $dcSet);

        $this->assertSame($dcSet->environmentInstall, $directory->environmentInstall('docker-compose'));
        $this->assertSame($dcSet->registryReinstall, $directory->registryReinstall('docker-compose'));
    }

    public function testUnknownTypeThrowsUnsupportedClusterTypeException(): void
    {
        $directory = (new ProvisioningPlanDirectory())->register('kubernetes', $this->buildSet());

        $this->expectException(UnsupportedClusterTypeException::class);

        $directory->environmentInstall('does-not-exist');
    }
}
