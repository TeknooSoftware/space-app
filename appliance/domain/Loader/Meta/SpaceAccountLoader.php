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

namespace Teknoo\Space\Loader\Meta;

use DomainException;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Query\QueryCollectionInterface;
use Teknoo\East\Common\Contracts\Query\QueryElementInterface;
use Teknoo\East\Common\Object\Collection\LazyLoadableCollection;
use Teknoo\East\Paas\Loader\AccountLoader;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Loader\AccountDataLoader;
use Teknoo\Space\Loader\AccountPersistedVariableLoader;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountData;
use Teknoo\Space\Query\AccountData\LoadFromAccountQuery;
use Teknoo\Space\Query\AccountPersistedVariable\LoadFromAccountQuery as LoadPVFromAccountQuery;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements LoaderInterface<SpaceAccount>
 */
class SpaceAccountLoader implements LoaderInterface
{
    public function __construct(
        private readonly AccountLoader $accountLoader,
        private readonly AccountDataLoader $dataLoader,
        private readonly AccountPersistedVariableLoader $accountPersistedVariableLoader,
    ) {
    }

    /**
     * @param PromiseInterface<SpaceAccount, mixed> $promise
     */
    private function fetchData(Account $account, PromiseInterface $promise): self
    {
        $accountPVLoader = $this->accountPersistedVariableLoader;

        /** @var Promise<AccountData, mixed, SpaceAccount> $fetchedPromise */
        $fetchedPromise = new Promise(
            static function (AccountData $data, PromiseInterface $next) use ($account, $accountPVLoader): void {
                $next->success(
                    new SpaceAccount(
                        $account,
                        $data,
                        new LazyLoadableCollection(
                            $accountPVLoader,
                            new LoadPVFromAccountQuery(
                                $account,
                            ),
                        ),
                    ),
                );
            },
            static fn (Throwable $error, PromiseInterface $next): PromiseInterface => $next->success(
                new SpaceAccount(
                    $account,
                    new AccountData(
                        account: $account,
                    ),
                    new LazyLoadableCollection(
                        $accountPVLoader,
                        new LoadPVFromAccountQuery(
                            $account,
                        ),
                    ),
                ),
            ),
            true
        );

        $this->dataLoader->fetch(
            new LoadFromAccountQuery($account),
            $fetchedPromise->next($promise)
        );

        return $this;
    }

    public function load(string $id, PromiseInterface $promise): LoaderInterface
    {
        /** @var Promise<Account, mixed, SpaceAccount> $fetchedPromise */
        $fetchedPromise = new Promise(
            fn (Account $account, PromiseInterface $next): SpaceAccountLoader => $this->fetchData($account, $next),
            static fn (Throwable $error, PromiseInterface $next): PromiseInterface => $next->fail(
                new DomainException(
                    message: 'teknoo.space.error.space_account.account.fetching',
                    code: $error->getCode() > 0 ? $error->getCode() : 404,
                    previous: $error,
                )
            ),
            true
        );

        $this->accountLoader->load(
            $id,
            $fetchedPromise->next($promise)
        );
        return $this;
    }

    public function query(QueryCollectionInterface $query, PromiseInterface $promise): LoaderInterface
    {
        /** @var Promise<iterable<Account>, mixed, iterable<SpaceAccount>> $fetchedPromise */
        $fetchedPromise = new Promise(
            static function (iterable $result) {
                /** @var iterable<Account> $result */
                $final = [];
                foreach ($result as $account) {
                    $final[] = new SpaceAccount($account, null); //Not needed actually to fetch metdata
                }

                return $final;
            },
            allowNext: true,
        );

        $this->accountLoader->query(
            $query,
            $fetchedPromise->next($promise, true)
        );

        return $this;
    }

    public function fetch(QueryElementInterface $query, PromiseInterface $promise): LoaderInterface
    {
        /** @var Promise<Account, mixed, SpaceAccount> $fetchedPromise */
        $fetchedPromise = new Promise(
            function (Account $account, PromiseInterface $next): void {
                $this->fetchData($account, $next);
            },
            static fn (Throwable $error, PromiseInterface $next): PromiseInterface => $next->fail(
                new DomainException(
                    message: 'teknoo.space.error.space_account.account_data.fetching',
                    code: $error->getCode() > 0 ? $error->getCode() : 404,
                    previous: $error,
                )
            ),
            true
        );

        $this->accountLoader->fetch(
            $query,
            $fetchedPromise->next($promise)
        );

        return $this;
    }
}
