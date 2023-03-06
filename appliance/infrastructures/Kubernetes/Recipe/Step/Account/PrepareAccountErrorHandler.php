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

namespace Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account;

use DateTimeInterface;
use Teknoo\East\Common\Service\DatesService;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Writer\AccountHistoryWriter;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class PrepareAccountErrorHandler
{
    public function __construct(
        private DatesService $datesService,
        private AccountHistoryWriter $writer,
        private bool $prefereRealDate,
    ) {
    }

    public function __invoke(
        Throwable $error,
        ManagerInterface $manager,
        AccountHistory $accountHistory
    ): self {
        $this->datesService->passMeTheDate(
            function (DateTimeInterface $dateTime) use ($accountHistory, $error) {
                $accountHistory->addToHistory(
                    $error->getMessage(),
                    $dateTime,
                    false,
                );

                $this->writer->save($accountHistory);
            },
            $this->prefereRealDate,
        );

        $manager->stopErrorReporting();

        return $this;
    }
}
