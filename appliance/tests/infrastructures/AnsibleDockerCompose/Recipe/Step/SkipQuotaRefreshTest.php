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

namespace Teknoo\Space\Tests\Unit\Infrastructures\AnsibleDockerCompose\Recipe\Step;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Space\Infrastructures\AnsibleDockerCompose\Recipe\Step\SkipQuotaRefresh;
use Teknoo\Space\Object\Persisted\AccountHistory;

/**
 * Class SkipQuotaRefreshTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(SkipQuotaRefresh::class)]
class SkipQuotaRefreshTest extends TestCase
{
    public function testInvokeOnlyRecordsThatTheRefreshIsNotApplicable(): void
    {
        $date = new DateTimeImmutable('2026-09-11 10:00:00');

        $datesService = $this->createMock(DatesService::class);
        $datesService->expects($this->once())
            ->method('passMeTheDate')
            ->with($this->isCallable(), true)
            ->willReturnCallback(
                static function (callable $setter) use ($date, $datesService): DatesService {
                    $setter($date);

                    return $datesService;
                }
            );

        $accountHistory = $this->createMock(AccountHistory::class);
        $accountHistory->expects($this->once())
            ->method('addToHistory')
            ->with('teknoo.space.text.account.docker_compose.quota_not_applicable', $date, false)
            ->willReturnSelf();

        $step = new SkipQuotaRefresh($datesService, true);

        $this->assertInstanceOf(
            SkipQuotaRefresh::class,
            $step($accountHistory),
        );
    }
}
