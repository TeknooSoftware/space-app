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

namespace Teknoo\Space\Recipe\Step\AccountRegistry;

use DomainException;
use RuntimeException;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Loader\AccountRegistryLoader;
use Teknoo\Space\Object\Persisted\AccountRegistry;
use Teknoo\Space\Query\AccountRegistry\LoadFromAccountQuery;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LoadRegistryCredential
{
    public function __construct(
        private readonly AccountRegistryLoader $loader,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        ?Account $accountInstance = null,
        bool $allowEmptyCredentials = false
    ): self {
        if (true === $allowEmptyCredentials && null === $accountInstance) {
            return $this;
        }

        $errorCallback = null;

        if (false === $allowEmptyCredentials) {
            $errorCallback = static fn (Throwable $error): ChefInterface => $manager->error(
                new DomainException(
                    message: 'teknoo.space.error.space_account.account_registry.fetching',
                    code: $error->getCode() > 0 ? $error->getCode() : 404,
                    previous: $error,
                )
            );

            if (null === $accountInstance) {
                $errorCallback(new RuntimeException('teknoo.space.error.space_account.missing'));

                return $this;
            }
        }

        /** @var Promise<AccountRegistry, mixed, mixed> $fetchedPromise */
        $fetchedPromise = new Promise(
            static fn (AccountRegistry $registry): ChefInterface => $manager->updateWorkPlan([
                AccountRegistry::class => $registry,
                'registryNamespace' => $registry->getRegistryNamespace(),
            ]),
            $errorCallback
        );

        $this->loader->fetch(
            new LoadFromAccountQuery($accountInstance),
            $fetchedPromise,
        );

        return $this;
    }
}
