<?php

/*
 * Teknoo Space.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Writer\Meta;

use RuntimeException;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Common\Object\User;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Object\DTO\SpaceUser;
use Teknoo\Space\Object\Persisted\UserData;
use Teknoo\Space\Writer\UserDataWriter;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements WriterInterface<SpaceUser>
 */
class SpaceUserWriter implements WriterInterface
{
    /**
     * @param WriterInterface<User> $userWriter
     */
    public function __construct(
        private WriterInterface $userWriter,
        private UserDataWriter $dataWriter,
    ) {
    }

    public function save(
        ObjectInterface $object,
        ?PromiseInterface $promise = null,
        ?bool $preferRealDateOnUpdate = null,
    ): WriterInterface {
        if (!$object instanceof SpaceUser) {
            $promise?->fail(new RuntimeException($object::class . 'is not supported by this writer', 500));

            return $this;
        }

        if (!$object->user instanceof User) {
            $promise?->fail(new RuntimeException(
                message: 'teknoo.space.error.space_user.writer.not_instantiable',
                code: 500
            ));

            return $this;
        }

        /** @var Promise<User, mixed, mixed> $persistedPromise */
        $persistedPromise = new Promise(
            function (User $user, PromiseInterface $next) use ($object, $preferRealDateOnUpdate) {
                if ($object->userData instanceof UserData) {
                    $data = $object->userData;
                    $data->setUser($object->user);

                    $this->dataWriter->save(object: $data, preferRealDateOnUpdate: $preferRealDateOnUpdate);
                    $next->success($user);
                }
            },
            static fn (Throwable $error, ?PromiseInterface $next = null) => $next?->fail(
                new RuntimeException(
                    message: 'teknoo.space.error.space_user.user.persisting',
                    code: $error->getCode() > 0 ? $error->getCode() : 500,
                    previous: $error,
                )
            ),
            true,
        );

        $this->userWriter->save(
            $object->user,
            $persistedPromise->next($promise),
            $preferRealDateOnUpdate
        );

        return $this;
    }

    public function remove(ObjectInterface $object, ?PromiseInterface $promise = null): WriterInterface
    {
        if (!$object instanceof SpaceUser) {
            $promise?->fail(new RuntimeException($object::class . 'is not supported by this writer', 500));

            return $this;
        }

        if ($object->userData instanceof UserData) {
            /** @var Promise<UserData, mixed, mixed> $removedPromise */
            $removedPromise = new Promise(
                function (mixed $result, ?PromiseInterface $next = null) use ($object) {
                    if ($object->user instanceof User) {
                        $this->userWriter->remove($object->user, $next);
                    }
                },
                static fn (Throwable $error, ?PromiseInterface $next = null) => $next?->fail(
                    new RuntimeException(
                        message: 'teknoo.space.error.space_user.user.deleting',
                        code: $error->getCode() > 0 ? $error->getCode() : 500,
                        previous: $error,
                    )
                ),
                true,
            );

            $this->dataWriter->remove(
                $object->userData,
                $removedPromise->next($promise)
            );
        }

        return $this;
    }
}
