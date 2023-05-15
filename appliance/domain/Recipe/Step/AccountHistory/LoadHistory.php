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

namespace Teknoo\Space\Recipe\Step\AccountHistory;

use DomainException;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\History;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Loader\AccountHistoryLoader;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Query\AccountHistory\LoadFromAccountQuery;
use Teknoo\Space\Writer\AccountHistoryWriter;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LoadHistory
{
    public function __construct(
        private AccountHistoryLoader $loader,
        private AccountHistoryWriter $writer,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        Account $accountInstance,
        ParametersBag $bag,
    ): self {
        $passHistory = static function (AccountHistory $accountHistory) use ($manager, $bag): void {
            $bag->set('accountHistory', $accountHistory);
            $accountHistory->passMeYouHistory(
                static function (History $history) use ($bag) {
                    $bag->set('accountHistoryRoot', $history);
                }
            );

            $manager->updateWorkPlan([
                AccountHistory::class => $accountHistory
            ]);
        };

        /** @var Promise<AccountHistory, mixed, mixed> $fetchedPromise */
        $fetchedPromise = new Promise(
            static function (AccountHistory $accountHistory) use ($passHistory) {
                $passHistory($accountHistory);
            },
            function (\Throwable $error) use ($manager, $passHistory, $accountInstance): void {
                if (!$error instanceof DomainException) {
                    $manager->error($error);

                    return;
                }

                $accountHistory = new AccountHistory($accountInstance);
                $this->writer->save($accountHistory);

                $passHistory($accountHistory);
            }
        );

        $this->loader->fetch(
            new LoadFromAccountQuery($accountInstance),
            $fetchedPromise
        );

        return $this;
    }
}
