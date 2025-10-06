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

namespace Teknoo\Space\Tests\Unit\Writer\Meta;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Teknoo\East\Common\Contracts\DBSource\BatchManipulationManagerInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Paas\Object\Project;
use Teknoo\East\Paas\Writer\ProjectWriter;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\Persisted\ProjectMetadata;
use Teknoo\Space\Object\Persisted\ProjectPersistedVariable;
use Teknoo\Space\Writer\Meta\SpaceProjectWriter;
use Teknoo\Space\Writer\ProjectPersistedVariableWriter;
use Teknoo\Space\Writer\ProjectMetadataWriter;
use Throwable;

/**
 * Class SpaceProjectWriterTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SpaceProjectWriter::class)]
class SpaceProjectWriterTest extends TestCase
{
    private SpaceProjectWriter $spaceProjectWriter;

    private ProjectWriter&MockObject $projectWriter;

    private ProjectMetadataWriter&MockObject $metadataWriter;

    private ProjectPersistedVariableWriter&MockObject $persistedVariableWriter;

    private BatchManipulationManagerInterface&MockObject $batchManipulationManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->projectWriter = $this->createMock(ProjectWriter::class);
        $this->metadataWriter = $this->createMock(ProjectMetadataWriter::class);
        $this->persistedVariableWriter = $this->createMock(ProjectPersistedVariableWriter::class);
        $this->batchManipulationManager = $this->createMock(BatchManipulationManagerInterface::class);
        $this->spaceProjectWriter = new SpaceProjectWriter(
            $this->projectWriter,
            $this->metadataWriter,
            $this->persistedVariableWriter,
            $this->batchManipulationManager
        );
    }

    public function testSaveWithWrongObject(): void
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof RuntimeException
                    && str_contains($error->getMessage(), 'is not supported by this writer')
                    && $error->getCode() === 500;
            }));

        $this->assertInstanceOf(
            SpaceProjectWriter::class,
            $this->spaceProjectWriter->save(
                $this->createMock(ObjectInterface::class),
                $promise,
                true,
            ),
        );
    }

    public function testSaveWithNullPromise(): void
    {
        $this->projectWriter->expects($this->never())
            ->method('save');

        $this->assertInstanceOf(
            SpaceProjectWriter::class,
            $this->spaceProjectWriter->save(
                $this->createMock(ObjectInterface::class),
                null,
                true,
            ),
        );
    }

    public function testSaveWithProjectMetadata(): void
    {
        $project = $this->createMock(Project::class);
        $projectMetadata = $this->createMock(ProjectMetadata::class);
        $projectMetadata->expects($this->once())
            ->method('setProject')
            ->with($project);

        $var1 = $this->createMock(ProjectPersistedVariable::class);
        $var1->expects($this->once())
            ->method('getId')
            ->willReturn('id1');
        $var2 = $this->createMock(ProjectPersistedVariable::class);
        $var2->expects($this->once())
            ->method('getId')
            ->willReturn('id2');

        $spaceProject = new SpaceProject($project, $projectMetadata, [$var1, $var2]);

        $this->projectWriter->expects($this->once())
            ->method('save')
            ->willReturnCallback(
                function ($obj, PromiseInterface $promise, $preferReal) use ($project) {
                    $promise->success($project);
                    return $this->projectWriter;
                }
            );

        $this->metadataWriter->expects($this->once())
            ->method('save')
            ->willReturnCallback(function ($metadata, PromiseInterface $promise, $preferReal) {
                $promise->success($metadata);
                return $this->metadataWriter;
            });

        $this->persistedVariableWriter->expects($this->exactly(2))
            ->method('save')
            ->willReturnCallback(function ($var, $promise, $preferReal) {
                $this->assertNull($promise);
                $this->assertTrue($preferReal);
                return $this->persistedVariableWriter;
            });

        $this->batchManipulationManager->expects($this->once())
            ->method('deleteQuery')
            ->with($this->anything(), $this->isInstanceOf(PromiseInterface::class));

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success');

        $this->assertInstanceOf(
            SpaceProjectWriter::class,
            $this->spaceProjectWriter->save(
                $spaceProject,
                $promise,
                true,
            ),
        );
    }

    public function testSaveWithoutProjectMetadata(): void
    {
        $project = $this->createMock(Project::class);
        $spaceProject = new SpaceProject($project, null, []);

        $this->projectWriter->expects($this->once())
            ->method('save')
            ->willReturnCallback(
                function ($obj, PromiseInterface $promise, $preferReal) use ($project) {
                    $promise->success($project);
                    return $this->projectWriter;
                }
            );

        $this->metadataWriter->expects($this->never())
            ->method('save');

        $this->batchManipulationManager->expects($this->once())
            ->method('deleteQuery');

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success');

        $this->assertInstanceOf(
            SpaceProjectWriter::class,
            $this->spaceProjectWriter->save(
                $spaceProject,
                $promise,
                true,
            ),
        );
    }

    public function testSaveWithProjectError(): void
    {
        $project = $this->createMock(Project::class);
        $spaceProject = new SpaceProject($project, null, []);

        $this->projectWriter->expects($this->once())
            ->method('save')
            ->willReturnCallback(
                function ($obj, PromiseInterface $promise) {
                    $promise->fail(new RuntimeException('error', 500));
                    return $this->projectWriter;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof RuntimeException
                    && $error->getMessage() === 'teknoo.space.error.space_project.project.persisting'
                    && $error->getCode() === 500;
            }));

        $this->assertInstanceOf(
            SpaceProjectWriter::class,
            $this->spaceProjectWriter->save(
                $spaceProject,
                $promise,
                true,
            ),
        );
    }

    public function testSaveWithProjectErrorDefaultCode(): void
    {
        $project = $this->createMock(Project::class);
        $spaceProject = new SpaceProject($project, null, []);

        $this->projectWriter->expects($this->once())
            ->method('save')
            ->willReturnCallback(
                function ($obj, PromiseInterface $promise) {
                    $promise->fail(new RuntimeException('error', 0));
                    return $this->projectWriter;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof RuntimeException
                    && $error->getMessage() === 'teknoo.space.error.space_project.project.persisting'
                    && $error->getCode() === 500;
            }));

        $this->assertInstanceOf(
            SpaceProjectWriter::class,
            $this->spaceProjectWriter->save(
                $spaceProject,
                $promise,
                true,
            ),
        );
    }

    public function testSaveWithProjectErrorWithoutPromise(): void
    {
        $project = $this->createMock(Project::class);
        $spaceProject = new SpaceProject($project, null, []);

        $this->projectWriter->expects($this->once())
            ->method('save')
            ->willReturnCallback(
                function ($obj, PromiseInterface $promise) {
                    $promise->fail(new RuntimeException('error', 500));
                    return $this->projectWriter;
                }
            );

        $this->assertInstanceOf(
            SpaceProjectWriter::class,
            $this->spaceProjectWriter->save(
                $spaceProject,
                null,
                true,
            ),
        );
    }

    public function testRemoveWithWrongObject(): void
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof RuntimeException
                    && str_contains($error->getMessage(), 'is not supported by this writer')
                    && $error->getCode() === 500;
            }));

        $this->assertInstanceOf(
            SpaceProjectWriter::class,
            $this->spaceProjectWriter->remove(
                $this->createMock(ObjectInterface::class),
                $promise,
            ),
        );
    }

    public function testRemoveWithNullPromise(): void
    {
        $this->projectWriter->expects($this->never())
            ->method('remove');

        $this->assertInstanceOf(
            SpaceProjectWriter::class,
            $this->spaceProjectWriter->remove(
                $this->createMock(ObjectInterface::class),
                null,
            ),
        );
    }

    public function testRemoveWithProjectMetadata(): void
    {
        $project = $this->createMock(Project::class);
        $projectMetadata = $this->createMock(ProjectMetadata::class);

        $var1 = $this->createMock(ProjectPersistedVariable::class);
        $var2 = $this->createMock(ProjectPersistedVariable::class);

        $spaceProject = new SpaceProject($project, $projectMetadata, [$var1, $var2]);

        $this->metadataWriter->expects($this->once())
            ->method('remove')
            ->willReturnCallback(
                function ($metadata, PromiseInterface $promise) {
                    $promise->success($metadata);
                    return $this->metadataWriter;
                }
            );

        $this->projectWriter->expects($this->once())
            ->method('remove')
            ->with($project, $this->isInstanceOf(PromiseInterface::class));

        $this->persistedVariableWriter->expects($this->exactly(2))
            ->method('remove')
            ->willReturnCallback(function ($var) {
                return $this->persistedVariableWriter;
            });

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success');

        $this->assertInstanceOf(
            SpaceProjectWriter::class,
            $this->spaceProjectWriter->remove(
                $spaceProject,
                $promise,
            ),
        );
    }

    public function testRemoveWithoutProjectMetadata(): void
    {
        $project = $this->createMock(Project::class);
        $spaceProject = new SpaceProject($project, null, []);

        $this->metadataWriter->expects($this->never())
            ->method('remove');

        $this->assertInstanceOf(
            SpaceProjectWriter::class,
            $this->spaceProjectWriter->remove(
                $spaceProject,
                $this->createMock(PromiseInterface::class),
            ),
        );
    }

    public function testRemoveWithMetadataError(): void
    {
        $project = $this->createMock(Project::class);
        $projectMetadata = $this->createMock(ProjectMetadata::class);
        $spaceProject = new SpaceProject($project, $projectMetadata, []);

        $this->metadataWriter->expects($this->once())
            ->method('remove')
            ->willReturnCallback(
                function ($metadata, PromiseInterface $promise) {
                    $promise->fail(new RuntimeException('error', 403));
                    return $this->metadataWriter;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof RuntimeException
                    && $error->getMessage() === 'teknoo.space.error.space_project.project.deleting'
                    && $error->getCode() === 403;
            }));

        $this->assertInstanceOf(
            SpaceProjectWriter::class,
            $this->spaceProjectWriter->remove(
                $spaceProject,
                $promise,
            ),
        );
    }

    public function testRemoveWithMetadataErrorDefaultCode(): void
    {
        $project = $this->createMock(Project::class);
        $projectMetadata = $this->createMock(ProjectMetadata::class);
        $spaceProject = new SpaceProject($project, $projectMetadata, []);

        $this->metadataWriter->expects($this->once())
            ->method('remove')
            ->willReturnCallback(
                function ($metadata, PromiseInterface $promise) {
                    $promise->fail(new RuntimeException('error', 0));
                    return $this->metadataWriter;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof RuntimeException
                    && $error->getMessage() === 'teknoo.space.error.space_project.project.deleting'
                    && $error->getCode() === 500;
            }));

        $this->assertInstanceOf(
            SpaceProjectWriter::class,
            $this->spaceProjectWriter->remove(
                $spaceProject,
                $promise,
            ),
        );
    }

    public function testRemoveWithMetadataErrorWithoutPromise(): void
    {
        $project = $this->createMock(Project::class);
        $projectMetadata = $this->createMock(ProjectMetadata::class);
        $spaceProject = new SpaceProject($project, $projectMetadata, []);

        $this->metadataWriter->expects($this->once())
            ->method('remove')
            ->willReturnCallback(
                function ($metadata, PromiseInterface $promise) {
                    $promise->fail(new RuntimeException('error', 500));
                    return $this->metadataWriter;
                }
            );

        $this->assertInstanceOf(
            SpaceProjectWriter::class,
            $this->spaceProjectWriter->remove(
                $spaceProject,
                null,
            ),
        );
    }
}
