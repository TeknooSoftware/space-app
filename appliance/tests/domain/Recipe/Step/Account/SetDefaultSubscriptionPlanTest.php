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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Account;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountData;
use Teknoo\Space\Recipe\Step\Account\SetDefaultSubscriptionPlan;

/**
 * Class SetDefaultSubscriptionPlanTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SetDefaultSubscriptionPlan::class)]
class SetDefaultSubscriptionPlanTest extends TestCase
{
    public function testInvokeWithAnEmptyDefaultPlan(): void
    {
        $accountData = $this->createMock(AccountData::class);
        $accountData->expects($this->never())
            ->method('setSubscriptionPlan');

        $spaceAccount = new SpaceAccount($this->createStub(Account::class));
        $spaceAccount->accountData = $accountData;

        $step = new SetDefaultSubscriptionPlan('');

        $this->assertInstanceOf(
            SetDefaultSubscriptionPlan::class,
            $step($spaceAccount),
        );
    }

    public function testInvokeThrowsExceptionWhenAccountDataIsNull(): void
    {
        $spaceAccount = new SpaceAccount($this->createStub(Account::class));
        $spaceAccount->accountData = null;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing Space Account data');

        (new SetDefaultSubscriptionPlan('plan-123'))($spaceAccount);
    }

    public function testInvokeSetsTheDefaultPlan(): void
    {
        $accountData = $this->createMock(AccountData::class);
        $accountData->expects($this->once())
            ->method('setSubscriptionPlan')
            ->with('plan-123');

        $spaceAccount = new SpaceAccount($this->createStub(Account::class));
        $spaceAccount->accountData = $accountData;

        $step = new SetDefaultSubscriptionPlan('plan-123');

        $this->assertInstanceOf(
            SetDefaultSubscriptionPlan::class,
            $step($spaceAccount),
        );
    }
}
