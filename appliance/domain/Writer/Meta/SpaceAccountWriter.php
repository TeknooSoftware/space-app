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

namespace Teknoo\Space\Writer\Meta;

use RuntimeException;
use Teknoo\East\Common\Contracts\DBSource\BatchManipulationManagerInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Writer\AccountWriter;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Loader\AccountCredentialLoader;
use Teknoo\Space\Loader\AccountHistoryLoader;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountCredential;
use Teknoo\Space\Object\Persisted\AccountData;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;
use Teknoo\Space\Query\AccountCredential\LoadFromAccountQuery as LoadCredentialsFromAccountQuery;
use Teknoo\Space\Query\AccountHistory\LoadFromAccountQuery as LoadHistoryFromAccountQuery;
use Teknoo\Space\Query\AccountPersistedVariable\DeleteVariablesQuery;
use Teknoo\Space\Writer\AccountCredentialWriter;
use Teknoo\Space\Writer\AccountDataWriter;
use Teknoo\Space\Writer\AccountHistoryWriter;
use Teknoo\Space\Writer\AccountPersistedVariableWriter;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements WriterInterface<SpaceAccount>
 */
class SpaceAccountWriter implements WriterInterface
{
    public function __construct(
        private AccountWriter $accountWriter,
        private AccountDataWriter $dataWriter,
        private AccountCredentialLoader $credentialLoader,
        private AccountHistoryLoader $historyLoader,
        private AccountCredentialWriter $credentialWriter,
        private AccountHistoryWriter $historyWriter,
        private AccountPersistedVariableWriter $accountPersistedVariableWriter,
        private BatchManipulationManagerInterface $batchManipulationManager,
    ) {
    }

    public function save(
        ObjectInterface $object,
        PromiseInterface $promise = null,
        ?bool $prefereRealDateOnUpdate = null,
    ): WriterInterface {
        if (!$object instanceof SpaceAccount) {
            $promise?->fail(new RuntimeException($object::class . 'is not supported by this writer', 500));

            return $this;
        }

        if (!$object->account instanceof Account) {
            $promise?->fail(
                new RuntimeException(
                    message: 'teknoo.space.error.space_account.writer.not_instantiable',
                    code: 500,
                )
            );

            return $this;
        }

        /** @var Promise<Account, mixed, mixed> $persistedPromise */
        $persistedPromise = new Promise(
            function (Account $account, PromiseInterface $next) use ($object, $prefereRealDateOnUpdate) {
                if ($object->accountData instanceof AccountData) {
                    $data = $object->accountData;
                    $data->setAccount($object->account);

                    $this->dataWriter->save(object: $data, prefereRealDateOnUpdate: $prefereRealDateOnUpdate);
                    $next->success($account);
                }

                $ids = [];
                foreach ($object->variables as $var) {
                    $this->accountPersistedVariableWriter->save(
                        object: $var,
                        prefereRealDateOnUpdate: $prefereRealDateOnUpdate
                    );
                    $ids[] = $var->getId();
                }

                /** @var Promise<AccountPersistedVariable, mixed, mixed> $deletedPromise */
                $deletedPromise = new Promise(
                    null,
                    fn (Throwable $error) => throw $error,
                );
                $this->batchManipulationManager->deleteQuery(
                    new DeleteVariablesQuery($object->account, $ids),
                    $deletedPromise,
                );
            },
            static fn (Throwable $error, ?PromiseInterface $next = null) => $next?->fail(
                new RuntimeException(
                    message: 'teknoo.space.error.space_account.account.persisting',
                    code: $error->getCode() > 0 ? $error->getCode() : 500,
                    previous: $error,
                )
            ),
            true,
        );

        $this->accountWriter->save(
            $object->account,
            $persistedPromise->next($promise),
            $prefereRealDateOnUpdate
        );

        return $this;
    }

    public function remove(ObjectInterface $object, PromiseInterface $promise = null): WriterInterface
    {
        if (!$object instanceof SpaceAccount) {
            $promise?->fail(new RuntimeException($object::class . 'is not supported by this writer', 500));

            return $this;
        }

        if ($object->accountData instanceof AccountData) {
            /** @var Promise<AccountData, mixed, mixed> $removedPromise */
            $removedPromise = new Promise(
                function (mixed $result, ?PromiseInterface $next = null) use ($object) {
                    if (!($account = $object->account) instanceof Account) {
                        return;
                    }

                    /** @var Promise<AccountCredential, mixed, mixed> $credentialsPromise */
                    $credentialsPromise = new Promise(
                        fn (AccountCredential $credential) => $this->credentialWriter->remove($credential),
                    );
                    $this->credentialLoader->fetch(
                        new LoadCredentialsFromAccountQuery($account),
                        $credentialsPromise,
                    );

                    /** @var Promise<AccountHistory, mixed, mixed> $historyPromise */
                    $historyPromise = new Promise(
                        fn (AccountHistory $history) => $this->historyWriter->remove($history),
                    );
                    $this->historyLoader->fetch(
                        new LoadHistoryFromAccountQuery($account),
                        $historyPromise,
                    );

                    $this->accountWriter->remove($account, $next);

                    foreach ($object->variables as $var) {
                        $this->accountPersistedVariableWriter->remove($var);
                    }
                },
                static fn (Throwable $error, ?PromiseInterface $next = null) => $next?->fail(
                    new RuntimeException(
                        message: 'teknoo.space.error.space_account.account.deleting',
                        code: $error->getCode() > 0 ? $error->getCode() : 500,
                        previous: $error,
                    )
                ),
                true,
            );

            $this->dataWriter->remove(
                $object->accountData,
                $removedPromise->next($promise)
            );
        }

        return $this;
    }
}
