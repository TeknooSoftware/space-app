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

namespace Teknoo\Space\Tests\Unit\Loader\Meta;

use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Query\QueryCollectionInterface;
use Teknoo\East\Common\Contracts\Query\QueryElementInterface;
use Teknoo\East\Paas\Loader\ProjectLoader;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Loader\Meta\SpaceProjectLoader;
use Teknoo\Space\Loader\ProjectPersistedVariableLoader;
use Teknoo\Space\Loader\ProjectMetadataLoader;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\Persisted\ProjectMetadata;
use Throwable;

/**
 * Class SpaceProjectLoaderTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SpaceProjectLoader::class)]
class SpaceProjectLoaderTest extends TestCase
{
    private SpaceProjectLoader $spaceProjectLoader;

    private ProjectLoader&MockObject $projectLoader;

    private ProjectMetadataLoader&MockObject $metadataLoader;

    private ProjectPersistedVariableLoader&MockObject $persistedVariableLoader;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->projectLoader = $this->createMock(ProjectLoader::class);
        $this->metadataLoader = $this->createMock(ProjectMetadataLoader::class);
        $this->persistedVariableLoader = $this->createMock(ProjectPersistedVariableLoader::class);
        $this->spaceProjectLoader = new SpaceProjectLoader(
            projectLoader: $this->projectLoader,
            metadataLoader: $this->metadataLoader,
            persistedVariableLoader: $this->persistedVariableLoader
        );
    }

    public function testLoad(): void
    {
        $this->projectLoader->expects($this->once())
            ->method('load')
            ->willReturnCallback(
                function (string $id, PromiseInterface $promise) {
                    $promise->success($this->createMock(Project::class));

                    return $this->projectLoader;
                }
            );

        $this->metadataLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->success($this->createMock(ProjectMetadata::class));

                    return $this->metadataLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->with($this->isInstanceOf(SpaceProject::class));

        $this->assertInstanceOf(
            SpaceProjectLoader::class,
            $this->spaceProjectLoader->load(
                'foo',
                $promise,
            ),
        );
    }

    public function testLoadWithProjectError(): void
    {
        $this->projectLoader->expects($this->once())
            ->method('load')
            ->willReturnCallback(
                function (string $id, PromiseInterface $promise) {
                    $promise->fail(new DomainException('error', 500));

                    return $this->projectLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof DomainException
                    && $error->getMessage() === 'teknoo.space.error.space_project.project.fetching'
                    && $error->getCode() === 500;
            }));

        $this->assertInstanceOf(
            SpaceProjectLoader::class,
            $this->spaceProjectLoader->load(
                'foo',
                $promise,
            ),
        );
    }

    public function testLoadWithProjectErrorDefaultCode(): void
    {
        $this->projectLoader->expects($this->once())
            ->method('load')
            ->willReturnCallback(
                function (string $id, PromiseInterface $promise) {
                    $promise->fail(new DomainException('error', 0));

                    return $this->projectLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof DomainException
                    && $error->getMessage() === 'teknoo.space.error.space_project.project.fetching'
                    && $error->getCode() === 404;
            }));

        $this->assertInstanceOf(
            SpaceProjectLoader::class,
            $this->spaceProjectLoader->load(
                'foo',
                $promise,
            ),
        );
    }

    public function testLoadWithMetadataError(): void
    {
        $this->projectLoader->expects($this->once())
            ->method('load')
            ->willReturnCallback(
                function (string $id, PromiseInterface $promise) {
                    $promise->success($this->createMock(Project::class));

                    return $this->projectLoader;
                }
            );

        $this->metadataLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->fail(new DomainException('metadata error', 500));

                    return $this->metadataLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof DomainException
                    && $error->getMessage() === 'teknoo.space.error.space_project.project_metadata.fetching'
                    && $error->getCode() === 500;
            }));

        $this->assertInstanceOf(
            SpaceProjectLoader::class,
            $this->spaceProjectLoader->load(
                'foo',
                $promise,
            ),
        );
    }

    public function testLoadWithMetadataErrorDefaultCode(): void
    {
        $this->projectLoader->expects($this->once())
            ->method('load')
            ->willReturnCallback(
                function (string $id, PromiseInterface $promise) {
                    $promise->success($this->createMock(Project::class));

                    return $this->projectLoader;
                }
            );

        $this->metadataLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->fail(new DomainException('metadata error', 0));

                    return $this->metadataLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof DomainException
                    && $error->getMessage() === 'teknoo.space.error.space_project.project_metadata.fetching'
                    && $error->getCode() === 404;
            }));

        $this->assertInstanceOf(
            SpaceProjectLoader::class,
            $this->spaceProjectLoader->load(
                'foo',
                $promise,
            ),
        );
    }

    public function testQuery(): void
    {
        $project1 = $this->createMock(Project::class);
        $project2 = $this->createMock(Project::class);

        $this->projectLoader->expects($this->once())
            ->method('query')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) use ($project1, $project2) {
                    $promise->success([$project1, $project2]);

                    return $this->projectLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->with($this->callback(function (array $result) {
                return count($result) === 2
                    && $result[0] instanceof SpaceProject
                    && $result[1] instanceof SpaceProject;
            }));

        $this->assertInstanceOf(
            SpaceProjectLoader::class,
            $this->spaceProjectLoader->query(
                $this->createMock(QueryCollectionInterface::class),
                $promise,
            ),
        );
    }

    public function testFetch(): void
    {
        $this->projectLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->success($this->createMock(Project::class));

                    return $this->projectLoader;
                }
            );

        $this->metadataLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->success($this->createMock(ProjectMetadata::class));

                    return $this->metadataLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->with($this->isInstanceOf(SpaceProject::class));

        $this->assertInstanceOf(
            SpaceProjectLoader::class,
            $this->spaceProjectLoader->fetch(
                $this->createMock(QueryElementInterface::class),
                $promise,
            ),
        );
    }

    public function testFetchWithProjectError(): void
    {
        $this->projectLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->fail(new DomainException('error', 403));

                    return $this->projectLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof DomainException
                    && $error->getMessage() === 'teknoo.space.error.space_project.project_metadata.fetching'
                    && $error->getCode() === 403;
            }));

        $this->assertInstanceOf(
            SpaceProjectLoader::class,
            $this->spaceProjectLoader->fetch(
                $this->createMock(QueryElementInterface::class),
                $promise,
            ),
        );
    }

    public function testFetchWithProjectErrorDefaultCode(): void
    {
        $this->projectLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->fail(new DomainException('error', 0));

                    return $this->projectLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof DomainException
                    && $error->getMessage() === 'teknoo.space.error.space_project.project_metadata.fetching'
                    && $error->getCode() === 404;
            }));

        $this->assertInstanceOf(
            SpaceProjectLoader::class,
            $this->spaceProjectLoader->fetch(
                $this->createMock(QueryElementInterface::class),
                $promise,
            ),
        );
    }
}
