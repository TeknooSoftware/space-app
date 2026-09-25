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

namespace Teknoo\Space\Recipe\Step\Task;

use DateTimeInterface;
use ReflectionClass;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Space\Contracts\DTO\NewTaskInterface;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Writer\AccountHistoryWriter;

use function array_diff_key;

/**
 * Record in the account history that a provisioning task has been queued to the `new_task` worker. The
 * result itself (success or error) is written later by the worker, so the refreshed account page already
 * shows that something is pending.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AddTaskToHistory
{
    public function __construct(
        private readonly AccountHistoryWriter $writer,
        private readonly DatesService $datesService,
        private readonly bool $preferRealDate,
    ) {
    }

    public function __invoke(
        NewTaskInterface $newTask,
        AccountHistory $accountHistory,
    ): self {
        $this->datesService->passMeTheDate(
            function (DateTimeInterface $dateTime) use ($newTask, $accountHistory): void {
                $accountHistory->addToHistory(
                    'teknoo.space.text.account.task.queued',
                    $dateTime,
                    false,
                    ['task' => new ReflectionClass($newTask)->getShortName()]
                        + array_diff_key($newTask->toArray(), ['extra' => true]),
                );

                $this->writer->save($accountHistory);
            },
            $this->preferRealDate,
        );

        return $this;
    }
}
