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
use Teknoo\Space\Recipe\Step\Job\IncludeExtraInWorkplan;

/**
 * Class IncludeExtraInWorkplanTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(IncludeExtraInWorkplan::class)]
class IncludeExtraInWorkplanTest extends TestCase
{
    private IncludeExtraInWorkplan $includeExtraInWorkplan;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();


        $this->includeExtraInWorkplan = new IncludeExtraInWorkplan();
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            IncludeExtraInWorkplan::class,
            ($this->includeExtraInWorkplan)(
                manager: $this->createStub(ManagerInterface::class),
                extra: ['foo' => 'bar'],
            )
        );
    }

    public function testInvokeWithEmptyExtra(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $result = ($this->includeExtraInWorkplan)(
            manager: $manager,
            extra: [],
        );

        $this->assertInstanceOf(IncludeExtraInWorkplan::class, $result);
    }

    public function testInvokeWithOciRegistryConfig(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan['ociRegistryConfig'])
                        && 'my-config' === $workplan['ociRegistryConfig']
                        && 1 === count($workplan);
                })
            );

        $result = ($this->includeExtraInWorkplan)(
            manager: $manager,
            extra: ['ociRegistryConfig' => 'my-config'],
        );

        $this->assertInstanceOf(IncludeExtraInWorkplan::class, $result);
    }

    public function testInvokeWithStorageIdentifier(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan['storageIdentifier'])
                        && 'storage-123' === $workplan['storageIdentifier']
                        && 1 === count($workplan);
                })
            );

        $result = ($this->includeExtraInWorkplan)(
            manager: $manager,
            extra: ['storageIdentifier' => 'storage-123'],
        );

        $this->assertInstanceOf(IncludeExtraInWorkplan::class, $result);
    }

    public function testInvokeWithBothKeys(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan['ociRegistryConfig'])
                        && 'my-config' === $workplan['ociRegistryConfig']
                        && isset($workplan['storageIdentifier'])
                        && 'storage-123' === $workplan['storageIdentifier']
                        && 2 === count($workplan);
                })
            );

        $result = ($this->includeExtraInWorkplan)(
            manager: $manager,
            extra: [
                'ociRegistryConfig' => 'my-config',
                'storageIdentifier' => 'storage-123',
            ],
        );

        $this->assertInstanceOf(IncludeExtraInWorkplan::class, $result);
    }

    public function testInvokeWithOtherKeys(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $result = ($this->includeExtraInWorkplan)(
            manager: $manager,
            extra: ['otherKey' => 'value', 'anotherKey' => 'value2'],
        );

        $this->assertInstanceOf(IncludeExtraInWorkplan::class, $result);
    }

    public function testInvokeWithEmptyValues(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $result = ($this->includeExtraInWorkplan)(
            manager: $manager,
            extra: ['ociRegistryConfig' => '', 'storageIdentifier' => ''],
        );

        $this->assertInstanceOf(IncludeExtraInWorkplan::class, $result);
    }

    public function testInvokeWithMixedKeys(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan['ociRegistryConfig'])
                        && 'my-config' === $workplan['ociRegistryConfig']
                        && !isset($workplan['otherKey'])
                        && 1 === count($workplan);
                })
            );

        $result = ($this->includeExtraInWorkplan)(
            manager: $manager,
            extra: [
                'ociRegistryConfig' => 'my-config',
                'otherKey' => 'value',
            ],
        );

        $this->assertInstanceOf(IncludeExtraInWorkplan::class, $result);
    }

    public function testInvokeWithIntegerValues(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan['ociRegistryConfig'])
                        && 123 === $workplan['ociRegistryConfig']
                        && isset($workplan['storageIdentifier'])
                        && 456 === $workplan['storageIdentifier']
                        && 2 === count($workplan);
                })
            );

        $result = ($this->includeExtraInWorkplan)(
            manager: $manager,
            extra: [
                'ociRegistryConfig' => 123,
                'storageIdentifier' => 456,
            ],
        );

        $this->assertInstanceOf(IncludeExtraInWorkplan::class, $result);
    }

    public function testInvokeWithZeroValues(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $result = ($this->includeExtraInWorkplan)(
            manager: $manager,
            extra: ['ociRegistryConfig' => 0, 'storageIdentifier' => 0],
        );

        $this->assertInstanceOf(IncludeExtraInWorkplan::class, $result);
    }
}
