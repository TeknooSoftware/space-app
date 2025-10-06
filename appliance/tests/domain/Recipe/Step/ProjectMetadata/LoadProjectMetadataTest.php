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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\ProjectMetadata;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Loader\ProjectMetadataLoader;
use Teknoo\Space\Object\Persisted\ProjectMetadata;
use Teknoo\Space\Query\ProjectMetadata\LoadFromProjectQuery;
use Teknoo\Space\Recipe\Step\ProjectMetadata\LoadProjectMetadata;

/**
 * Class LoadProjectMetadataTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(LoadProjectMetadata::class)]
class LoadProjectMetadataTest extends TestCase
{
    private LoadProjectMetadata $loadProjectMetadata;

    private ProjectMetadataLoader&MockObject $loader;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = $this->createMock(ProjectMetadataLoader::class);
        $this->loadProjectMetadata = new LoadProjectMetadata($this->loader);
    }

    public function testInvoke(): void
    {
        $this->loader->expects($this->once())
            ->method('fetch')
            ->with(
                $this->isInstanceOf(LoadFromProjectQuery::class),
                $this->isInstanceOf(Promise::class)
            );

        $this->assertInstanceOf(
            LoadProjectMetadata::class,
            ($this->loadProjectMetadata)(
                $this->createMock(ManagerInterface::class),
                $this->createMock(Project::class),
                $this->createMock(ParametersBag::class),
            )
        );
    }

    public function testInvokeWithPromiseSuccess(): void
    {
        $metadata = $this->createMock(ProjectMetadata::class);

        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->once())
            ->method('set')
            ->with('projectMetadata', $this->identicalTo($metadata));

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with($this->callback(function ($data) use ($metadata) {
                return isset($data[ProjectMetadata::class])
                    && $data[ProjectMetadata::class] === $metadata;
            }));

        $this->loader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($query, $promise) use ($metadata) {
                $promise->success($metadata);

                return $this->loader;
            });

        $this->assertInstanceOf(
            LoadProjectMetadata::class,
            ($this->loadProjectMetadata)(
                $manager,
                $this->createMock(Project::class),
                $bag,
            )
        );
    }
}
