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

namespace Teknoo\Space\Recipe\Step\Account;

use DateTimeInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Writer\AccountHistoryWriter;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CreateAccountHistory
{
    public function __construct(
        private AccountHistoryWriter $writer,
        private DatesService $datesService,
        private bool $prefereRealDate,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        Account $accountInstance,
        string $accountNamespace,
        ?AccountHistory $accountHistory = null,
    ): self {
        if ($accountHistory instanceof AccountHistory) {
            return $this;
        }

        $accountHistory = new AccountHistory($accountInstance);
        $this->datesService->passMeTheDate(
            static function (DateTimeInterface $dateTime) use ($accountHistory, $accountNamespace) {
                $accountHistory->addToHistory(
                    'teknoo.space.text.account.create',
                    $dateTime,
                    false,
                    [
                        'namespace' => $accountNamespace,
                    ]
                );
            },
            $this->prefereRealDate,
        );

        $this->writer->save($accountHistory);

        $manager->updateWorkPlan([
            AccountHistory::class => $accountHistory
        ]);

        return $this;
    }
}
