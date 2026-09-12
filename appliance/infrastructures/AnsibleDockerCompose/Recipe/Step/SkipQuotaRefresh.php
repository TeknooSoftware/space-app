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

namespace Teknoo\Space\Infrastructures\AnsibleDockerCompose\Recipe\Step;

use DateTimeInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Space\Object\Persisted\AccountHistory;

/**
 * A Docker host has no `ResourceQuota` to reconcile, so a quota refresh on a docker-compose cluster has
 * nothing to apply. This step only records that fact in the account history, so the operator does not read
 * a silent success. The history is persisted by the task plan's `UpdateAccountHistory`.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SkipQuotaRefresh
{
    public function __construct(
        private readonly DatesService $datesService,
        private readonly bool $preferRealDate,
    ) {
    }

    public function __invoke(
        AccountHistory $accountHistory,
    ): self {
        $this->datesService->passMeTheDate(
            static function (DateTimeInterface $dateTime) use ($accountHistory): void {
                $accountHistory->addToHistory(
                    'teknoo.space.text.account.docker_compose.quota_not_applicable',
                    $dateTime,
                    false,
                );
            },
            $this->preferRealDate,
        );

        return $this;
    }
}
