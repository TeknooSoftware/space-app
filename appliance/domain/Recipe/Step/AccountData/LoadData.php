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
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Recipe\Step\AccountData;

use DomainException;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Loader\AccountDataLoader;
use Teknoo\Space\Object\Persisted\AccountData;
use Teknoo\Space\Query\AccountData\LoadFromAccountQuery;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LoadData
{
    public function __construct(
        private AccountDataLoader $loader,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        Account $accountInstance,
        bool $allowEmptyDatas = false
    ): self {
        $errorCallback = null;

        if (false === $allowEmptyDatas) {
            $errorCallback = static fn (Throwable $error) => $manager->error(
                new DomainException(
                    message: 'teknoo.space.error.space_account.account_data.fetching',
                    code: $error->getCode() > 0 ? $error->getCode() : 404,
                    previous: $error,
                )
            );
        }

        /** @var Promise<AccountData, mixed, mixed> $fetchedPromise */
        $fetchedPromise = new Promise(
            static function (AccountData $accountData) use ($manager) {
                $manager->updateWorkPlan([AccountData::class => $accountData]);
            },
            $errorCallback
        );

        $this->loader->fetch(
            new LoadFromAccountQuery($accountInstance),
            $fetchedPromise,
        );

        return $this;
    }
}
