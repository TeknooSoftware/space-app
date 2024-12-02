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

namespace Teknoo\Space\Loader\Meta;

use DomainException;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Query\QueryCollectionInterface;
use Teknoo\East\Common\Contracts\Query\QueryElementInterface;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Object\User;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Loader\UserDataLoader;
use Teknoo\Space\Object\DTO\SpaceUser;
use Teknoo\Space\Object\Persisted\UserData;
use Teknoo\Space\Query\UserData\LoadFromUserQuery;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements LoaderInterface<SpaceUser>
 */
class SpaceUserLoader implements LoaderInterface
{
    public function __construct(
        private UserLoader $userLoader,
        private UserDataLoader $dataLoader,
    ) {
    }

    /**
     * @param User $user
     * @param PromiseInterface<SpaceUser, mixed> $promise
     */
    private function fetchData(User $user, PromiseInterface $promise): self
    {
        /** @var Promise<UserData, mixed, SpaceUser> $fetchedPromise */
        $fetchedPromise = new Promise(
            static function (UserData $data, PromiseInterface $next) use ($user) {
                $next->success(new SpaceUser($user, $data));
            },
            static function (Throwable $error, PromiseInterface $next) use ($user) {
                $next->success(new SpaceUser($user, new UserData($user)));
            },
            true
        );

        $this->dataLoader->fetch(
            new LoadFromUserQuery($user),
            $fetchedPromise->next($promise)
        );

        return $this;
    }

    public function load(string $id, PromiseInterface $promise): LoaderInterface
    {
        /** @var Promise<User, mixed, SpaceUser> $fetchedPromise */
        $fetchedPromise = new Promise(
            fn (User $user, PromiseInterface $next) => $this->fetchData($user, $next),
            static fn (Throwable $error, PromiseInterface $next) => $next->fail(
                new DomainException(
                    message: 'teknoo.space.error.space_user.user.fetching',
                    code: $error->getCode() > 0 ? $error->getCode() : 404,
                    previous: $error,
                )
            ),
            true
        );

        $this->userLoader->load(
            $id,
            $fetchedPromise->next($promise)
        );
        return $this;
    }

    public function query(QueryCollectionInterface $query, PromiseInterface $promise): LoaderInterface
    {
        /** @var Promise<iterable<User>, mixed, iterable<SpaceUser>> $fetchedPromise */
        $fetchedPromise = new Promise(
            static function (iterable $result) {
                $final = [];
                foreach ($result as $user) {
                    $final[] = new SpaceUser($user, null); //Not needed actually to fetch metdata
                }

                return $final;
            },
            allowNext: true,
        );

        $this->userLoader->query(
            $query,
            $fetchedPromise->next($promise, true)
        );

        return $this;
    }

    public function fetch(QueryElementInterface $query, PromiseInterface $promise): LoaderInterface
    {
        /** @var Promise<User, mixed, SpaceUser> $fetchedPromise */
        $fetchedPromise = new Promise(
            function (User $user, PromiseInterface $next) {
                $this->fetchData($user, $next);
            },
            static fn (Throwable $error, PromiseInterface $next) => $next->fail(
                new DomainException(
                    message: 'teknoo.space.error.space_user.user.fetching',
                    code: $error->getCode() > 0 ? $error->getCode() : 404,
                    previous: $error,
                )
            ),
            true
        );

        $this->userLoader->fetch(
            $query,
            $fetchedPromise->next($promise)
        );

        return $this;
    }
}
