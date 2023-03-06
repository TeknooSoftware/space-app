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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Recipe\Step\UserData;

use DomainException;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Loader\UserDataLoader;
use Teknoo\Space\Object\Persisted\UserData;
use Teknoo\Space\Query\UserData\LoadFromUserQuery;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LoadData
{
    public function __construct(
        private UserDataLoader $loader,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        User $userInstance,
        bool $allowEmptyDatas = false
    ): self {
        $errorCallback = null;

        if (false === $allowEmptyDatas) {
            $errorCallback = static fn (Throwable $error) => $manager->error(
                new DomainException(
                    message: 'teknoo.space.error.space_user.user_data.fetching',
                    code: $error->getCode() > 0 ? $error->getCode() : 404,
                    previous: $error,
                )
            );
        }

        /** @var Promise<UserData, mixed, mixed> $fetchedPromise */
        $fetchedPromise = new Promise(
            static function (UserData $userData) use ($manager) {
                $manager->updateWorkPlan([UserData::class => $userData]);
            },
            $errorCallback
        );

        $this->loader->fetch(
            new LoadFromUserQuery($userInstance),
            $fetchedPromise,
        );

        return $this;
    }
}
