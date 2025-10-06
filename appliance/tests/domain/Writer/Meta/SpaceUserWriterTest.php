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
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Common\Object\User;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Object\DTO\SpaceUser;
use Teknoo\Space\Object\Persisted\UserData;
use Teknoo\Space\Writer\Meta\SpaceUserWriter;
use Teknoo\Space\Writer\UserDataWriter;
use Throwable;

/**
 * Class SpaceUserWriterTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SpaceUserWriter::class)]
class SpaceUserWriterTest extends TestCase
{
    private SpaceUserWriter $spaceUserWriter;

    private WriterInterface&MockObject $userWriter;

    private UserDataWriter&MockObject $dataWriter;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userWriter = $this->createMock(WriterInterface::class);
        $this->dataWriter = $this->createMock(UserDataWriter::class);
        $this->spaceUserWriter = new SpaceUserWriter($this->userWriter, $this->dataWriter);
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
            SpaceUserWriter::class,
            $this->spaceUserWriter->save(
                $this->createMock(ObjectInterface::class),
                $promise,
                true,
            ),
        );
    }

    public function testSaveWithNullPromise(): void
    {
        $this->userWriter->expects($this->never())
            ->method('save');

        $this->assertInstanceOf(
            SpaceUserWriter::class,
            $this->spaceUserWriter->save(
                $this->createMock(ObjectInterface::class),
                null,
                true,
            ),
        );
    }

    public function testSaveWithUserData(): void
    {
        $user = $this->createMock(User::class);
        $userData = $this->createMock(UserData::class);
        $userData->expects($this->once())
            ->method('setUser')
            ->with($user);

        $spaceUser = new SpaceUser($user, $userData);

        $this->userWriter->expects($this->once())
            ->method('save')
            ->willReturnCallback(
                function ($obj, PromiseInterface $promise, $preferReal) use ($user) {
                    $promise->success($user);
                    return $this->userWriter;
                }
            );

        $this->dataWriter->expects($this->once())
            ->method('save')
            ->with($userData, null, true);

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->with($user);

        $this->assertInstanceOf(
            SpaceUserWriter::class,
            $this->spaceUserWriter->save(
                $spaceUser,
                $promise,
                true,
            ),
        );
    }


    public function testSaveWithUserError(): void
    {
        $user = $this->createMock(User::class);
        $spaceUser = new SpaceUser($user, null);

        $this->userWriter->expects($this->once())
            ->method('save')
            ->willReturnCallback(
                function ($obj, PromiseInterface $promise) {
                    $promise->fail(new RuntimeException('error', 500));
                    return $this->userWriter;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof RuntimeException
                    && $error->getMessage() === 'teknoo.space.error.space_user.user.persisting'
                    && $error->getCode() === 500;
            }));

        $this->assertInstanceOf(
            SpaceUserWriter::class,
            $this->spaceUserWriter->save(
                $spaceUser,
                $promise,
                true,
            ),
        );
    }

    public function testSaveWithUserErrorDefaultCode(): void
    {
        $user = $this->createMock(User::class);
        $spaceUser = new SpaceUser($user, null);

        $this->userWriter->expects($this->once())
            ->method('save')
            ->willReturnCallback(
                function ($obj, PromiseInterface $promise) {
                    $promise->fail(new RuntimeException('error', 0));
                    return $this->userWriter;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof RuntimeException
                    && $error->getMessage() === 'teknoo.space.error.space_user.user.persisting'
                    && $error->getCode() === 500;
            }));

        $this->assertInstanceOf(
            SpaceUserWriter::class,
            $this->spaceUserWriter->save(
                $spaceUser,
                $promise,
                true,
            ),
        );
    }

    public function testSaveWithUserErrorWithoutPromise(): void
    {
        $user = $this->createMock(User::class);
        $spaceUser = new SpaceUser($user, null);

        $this->userWriter->expects($this->once())
            ->method('save')
            ->willReturnCallback(
                function ($obj, PromiseInterface $promise) {
                    $promise->fail(new RuntimeException('error', 500));
                    return $this->userWriter;
                }
            );

        $this->assertInstanceOf(
            SpaceUserWriter::class,
            $this->spaceUserWriter->save(
                $spaceUser,
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
            SpaceUserWriter::class,
            $this->spaceUserWriter->remove(
                $this->createMock(ObjectInterface::class),
                $promise,
            ),
        );
    }

    public function testRemoveWithNullPromise(): void
    {
        $this->userWriter->expects($this->never())
            ->method('remove');

        $this->assertInstanceOf(
            SpaceUserWriter::class,
            $this->spaceUserWriter->remove(
                $this->createMock(ObjectInterface::class),
                null,
            ),
        );
    }

    public function testRemoveWithUserData(): void
    {
        $user = $this->createMock(User::class);
        $userData = $this->createMock(UserData::class);
        $spaceUser = new SpaceUser($user, $userData);

        $this->dataWriter->expects($this->once())
            ->method('remove')
            ->willReturnCallback(
                function ($data, PromiseInterface $promise) {
                    $promise->success($data);
                    return $this->dataWriter;
                }
            );

        $this->userWriter->expects($this->once())
            ->method('remove')
            ->with($user, $this->isInstanceOf(PromiseInterface::class));

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success');

        $this->assertInstanceOf(
            SpaceUserWriter::class,
            $this->spaceUserWriter->remove(
                $spaceUser,
                $promise,
            ),
        );
    }


    public function testRemoveWithDataError(): void
    {
        $user = $this->createMock(User::class);
        $userData = $this->createMock(UserData::class);
        $spaceUser = new SpaceUser($user, $userData);

        $this->dataWriter->expects($this->once())
            ->method('remove')
            ->willReturnCallback(
                function ($data, PromiseInterface $promise) {
                    $promise->fail(new RuntimeException('error', 403));
                    return $this->dataWriter;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof RuntimeException
                    && $error->getMessage() === 'teknoo.space.error.space_user.user.deleting'
                    && $error->getCode() === 403;
            }));

        $this->assertInstanceOf(
            SpaceUserWriter::class,
            $this->spaceUserWriter->remove(
                $spaceUser,
                $promise,
            ),
        );
    }

    public function testRemoveWithDataErrorDefaultCode(): void
    {
        $user = $this->createMock(User::class);
        $userData = $this->createMock(UserData::class);
        $spaceUser = new SpaceUser($user, $userData);

        $this->dataWriter->expects($this->once())
            ->method('remove')
            ->willReturnCallback(
                function ($data, PromiseInterface $promise) {
                    $promise->fail(new RuntimeException('error', 0));
                    return $this->dataWriter;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof RuntimeException
                    && $error->getMessage() === 'teknoo.space.error.space_user.user.deleting'
                    && $error->getCode() === 500;
            }));

        $this->assertInstanceOf(
            SpaceUserWriter::class,
            $this->spaceUserWriter->remove(
                $spaceUser,
                $promise,
            ),
        );
    }

    public function testRemoveWithDataErrorWithoutPromise(): void
    {
        $user = $this->createMock(User::class);
        $userData = $this->createMock(UserData::class);
        $spaceUser = new SpaceUser($user, $userData);

        $this->dataWriter->expects($this->once())
            ->method('remove')
            ->willReturnCallback(
                function ($data, PromiseInterface $promise) {
                    $promise->fail(new RuntimeException('error', 500));
                    return $this->dataWriter;
                }
            );

        $this->assertInstanceOf(
            SpaceUserWriter::class,
            $this->spaceUserWriter->remove(
                $spaceUser,
                null,
            ),
        );
    }
}
