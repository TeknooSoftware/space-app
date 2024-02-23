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

namespace Teknoo\Space\Recipe\Step\AccountCredential;

use DomainException;
use RuntimeException;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Loader\AccountCredentialLoader;
use Teknoo\Space\Object\Persisted\AccountCredential;
use Teknoo\Space\Query\AccountCredential\LoadFromAccountQuery;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LoadCredentials
{
    public function __construct(
        private AccountCredentialLoader $loader,
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
            $errorCallback = static fn (Throwable $error) => $manager->error(
                new DomainException(
                    message: 'teknoo.space.error.space_account.account_credential.fetching',
                    code: $error->getCode() > 0 ? $error->getCode() : 404,
                    previous: $error,
                )
            );

            if (null === $accountInstance) {
                $errorCallback(new RuntimeException('teknoo.space.error.space_account.missing'));

                return $this;
            }
        }

        /** @var Promise<AccountCredential, mixed, mixed> $fetchedPromise */
        $fetchedPromise = new Promise(
            //todo Use AccountsCredentialsWallet
            static function (AccountCredential $accountCredential) use ($manager) {
                $manager->updateWorkPlan(
                    [
                        AccountCredential::class => $accountCredential,
                        //todo select from AccountsCredentialsWallet if needed, check if need
                        'persistentVolumeClaimName' => $accountCredential->getPersistentVolumeClaimName(),
                        'registryConfigName' => $accountCredential->getRegistryConfigName(),
                        'ociRegistryConfig' => $accountCredential->getRegistryConfigName(),
                    ],
                );
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
