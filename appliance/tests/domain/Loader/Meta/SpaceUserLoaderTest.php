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
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Query\QueryCollectionInterface;
use Teknoo\East\Common\Contracts\Query\QueryElementInterface;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Object\User;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Loader\Meta\SpaceUserLoader;
use Teknoo\Space\Loader\UserDataLoader;
use Teknoo\Space\Object\DTO\SpaceUser;
use Teknoo\Space\Object\Persisted\UserData;
use Throwable;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * Class SpaceUserLoaderTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SpaceUserLoader::class)]
class SpaceUserLoaderTest extends TestCase
{
    private SpaceUserLoader $spaceUserLoader;

    private UserLoader&MockObject $userLoader;

    private UserDataLoader&MockObject $dataLoader;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userLoader = $this->createMock(UserLoader::class);
        $this->dataLoader = $this->createMock(UserDataLoader::class);
        $this->spaceUserLoader = new SpaceUserLoader(
            userLoader: $this->userLoader,
            dataLoader: $this->dataLoader,
        );
    }

    public function testLoad(): void
    {
        $this->userLoader->expects($this->once())
            ->method('load')
            ->willReturnCallback(
                function (string $id, PromiseInterface $promise) {
                    $promise->success($this->createStub(User::class));

                    return $this->userLoader;
                }
            );

        $this->dataLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->success($this->createStub(UserData::class));

                    return $this->dataLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->with($this->isInstanceOf(SpaceUser::class));

        $this->assertInstanceOf(
            SpaceUserLoader::class,
            $this->spaceUserLoader->load(
                'foo',
                $promise,
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testLoadWithUserError(): void
    {
        $this->userLoader->expects($this->once())
            ->method('load')
            ->willReturnCallback(
                function (string $id, PromiseInterface $promise) {
                    $promise->fail(new DomainException('error', 500));

                    return $this->userLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof DomainException
                    && $error->getMessage() === 'teknoo.space.error.space_user.user.fetching'
                    && $error->getCode() === 500;
            }));

        $this->assertInstanceOf(
            SpaceUserLoader::class,
            $this->spaceUserLoader->load(
                'foo',
                $promise,
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testLoadWithUserErrorDefaultCode(): void
    {
        $this->userLoader->expects($this->once())
            ->method('load')
            ->willReturnCallback(
                function (string $id, PromiseInterface $promise) {
                    $promise->fail(new DomainException('error', 0));

                    return $this->userLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof DomainException
                    && $error->getMessage() === 'teknoo.space.error.space_user.user.fetching'
                    && $error->getCode() === 404;
            }));

        $this->assertInstanceOf(
            SpaceUserLoader::class,
            $this->spaceUserLoader->load(
                'foo',
                $promise,
            ),
        );
    }

    public function testLoadWithDataError(): void
    {
        $this->userLoader->expects($this->once())
            ->method('load')
            ->willReturnCallback(
                function (string $id, PromiseInterface $promise) {
                    $promise->success($this->createStub(User::class));

                    return $this->userLoader;
                }
            );

        $this->dataLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->fail(new DomainException('data error'));

                    return $this->dataLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->with($this->callback(function (SpaceUser $spaceUser) {
                return $spaceUser->userData instanceof UserData;
            }));

        $this->assertInstanceOf(
            SpaceUserLoader::class,
            $this->spaceUserLoader->load(
                'foo',
                $promise,
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testQuery(): void
    {
        $user1 = $this->createStub(User::class);
        $user2 = $this->createStub(User::class);

        $this->userLoader->expects($this->once())
            ->method('query')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) use ($user1, $user2) {
                    $promise->success([$user1, $user2]);

                    return $this->userLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->with($this->callback(function (array $result) {
                return count($result) === 2
                    && $result[0] instanceof SpaceUser
                    && $result[1] instanceof SpaceUser;
            }));

        $this->assertInstanceOf(
            SpaceUserLoader::class,
            $this->spaceUserLoader->query(
                $this->createStub(QueryCollectionInterface::class),
                $promise,
            ),
        );
    }

    public function testFetch(): void
    {
        $this->userLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->success($this->createStub(User::class));

                    return $this->userLoader;
                }
            );

        $this->dataLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->success($this->createStub(UserData::class));

                    return $this->dataLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->with($this->isInstanceOf(SpaceUser::class));

        $this->assertInstanceOf(
            SpaceUserLoader::class,
            $this->spaceUserLoader->fetch(
                $this->createStub(QueryElementInterface::class),
                $promise,
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testFetchWithUserError(): void
    {
        $this->userLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->fail(new DomainException('error', 403));

                    return $this->userLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof DomainException
                    && $error->getMessage() === 'teknoo.space.error.space_user.user.fetching'
                    && $error->getCode() === 403;
            }));

        $this->assertInstanceOf(
            SpaceUserLoader::class,
            $this->spaceUserLoader->fetch(
                $this->createStub(QueryElementInterface::class),
                $promise,
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testFetchWithUserErrorDefaultCode(): void
    {
        $this->userLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->fail(new DomainException('error', 0));

                    return $this->userLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof DomainException
                    && $error->getMessage() === 'teknoo.space.error.space_user.user.fetching'
                    && $error->getCode() === 404;
            }));

        $this->assertInstanceOf(
            SpaceUserLoader::class,
            $this->spaceUserLoader->fetch(
                $this->createStub(QueryElementInterface::class),
                $promise,
            ),
        );
    }
}
