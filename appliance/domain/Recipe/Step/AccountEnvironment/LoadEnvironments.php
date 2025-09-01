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

namespace Teknoo\Space\Recipe\Step\AccountEnvironment;

use DomainException;
use RuntimeException;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Loader\AccountEnvironmentLoader;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Query\AccountEnvironment\LoadFromAccountQuery;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LoadEnvironments
{
    public function __construct(
        private readonly AccountEnvironmentLoader $loader,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        Account|SpaceAccount|null $accountInstance = null,
        bool $allowEmptyCredentials = false
    ): self {
        if ($accountInstance instanceof SpaceAccount) {
            $accountInstance = $accountInstance->account;
        }

        if (true === $allowEmptyCredentials && null === $accountInstance) {
            return $this;
        }

        $errorCallback = fn (): ChefInterface => $manager->updateWorkPlan([
            AccountWallet::class => new AccountWallet([])
        ]);

        if (false === $allowEmptyCredentials) {
            $errorCallback = static fn (Throwable $error): ChefInterface => $manager->error(
                new DomainException(
                    message: 'teknoo.space.error.space_account.account_environment.fetching',
                    code: $error->getCode() > 0 ? $error->getCode() : 404,
                    previous: $error,
                )
            );

            if (null === $accountInstance) {
                $errorCallback(new RuntimeException('teknoo.space.error.space_account.missing'));

                return $this;
            }
        }

        /** @var Promise<iterable<AccountEnvironment>, mixed, mixed> $fetchedPromise */
        $fetchedPromise = new Promise(
            /** @var iterable<AccountEnvironment> */
            static function (iterable $credentials) use ($manager): void {
                /** @var iterable<AccountEnvironment> $credentials */
                $manager->updateWorkPlan([
                    AccountWallet::class => new AccountWallet($credentials),
                ]);
            },
            $errorCallback
        );

        $this->loader->query(
            new LoadFromAccountQuery($accountInstance),
            $fetchedPromise,
        );

        return $this;
    }
}
