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

namespace Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account;

use DateTimeInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Writer\AccountHistoryWriter;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ReinstallAccountErrorHandler
{
    public function __construct(
        private readonly DatesService $datesService,
        private readonly AccountHistoryWriter $writer,
        private readonly bool $preferRealDate,
    ) {
    }

    public function __invoke(
        Throwable $error,
        ManagerInterface $manager,
        AccountHistory $accountHistory
    ): self {
        $this->datesService->passMeTheDate(
            function (DateTimeInterface $dateTime) use ($accountHistory, $error): void {
                $accountHistory->addToHistory(
                    $error->getMessage(),
                    $dateTime,
                    false,
                );

                $this->writer->save($accountHistory);
            },
            $this->preferRealDate,
        );

        $manager->stopErrorReporting();

        return $this;
    }
}
