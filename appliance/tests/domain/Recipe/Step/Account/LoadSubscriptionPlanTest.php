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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\Config\SubscriptionPlan;
use Teknoo\Space\Object\Config\SubscriptionPlanCatalog;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountData;
use Teknoo\Space\Recipe\Step\Account\LoadSubscriptionPlan;

use function array_key_exists;

/**
 * Class loadSubscriptionPlanTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(LoadSubscriptionPlan::class)]
class LoadSubscriptionPlanTest extends TestCase
{
    private LoadSubscriptionPlan $loadSubscriptionPlan;

    private SubscriptionPlanCatalog&MockObject $subscriptionPlanCatalog;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriptionPlanCatalog = $this->createMock(SubscriptionPlanCatalog::class);

        $this->loadSubscriptionPlan = new LoadSubscriptionPlan(
            $this->subscriptionPlanCatalog,
        );
    }

    public function testInvoke(): void
    {
        $spaceAccount = new SpaceAccount($this->createMock(Account::class));
        $spaceAccount->accountData = $this->createMock(AccountData::class);

        $this->assertInstanceOf(
            LoadSubscriptionPlan::class,
            ($this->loadSubscriptionPlan)(
                $this->createMock(ManagerInterface::class),
                $spaceAccount,
            ),
        );
    }

    public function testInvokeWithMissingAccountData(): void
    {
        $spaceAccount = new SpaceAccount(
            account: $this->createMock(Account::class),
            accountData: null,
        );
        $spaceAccount->accountData = null;
        // accountData is null

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Missing Space Account data');

        ($this->loadSubscriptionPlan)(
            $this->createMock(ManagerInterface::class),
            $spaceAccount,
        );
    }

    public function testInvokeWithEmptyPlanId(): void
    {
        $accountData = $this->createMock(AccountData::class);
        $accountData->expects($this->once())
            ->method('visit')
            ->willReturnCallback(function ($field, $promise) use ($accountData) {
                $this->assertEquals('subscriptionPlan', $field);
                $promise->success('');
                return $accountData;
            });

        $spaceAccount = new SpaceAccount($this->createMock(Account::class));
        $spaceAccount->accountData = $accountData;

        $this->subscriptionPlanCatalog->expects($this->never())
            ->method('getSubscriptionPlan');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return array_key_exists(SubscriptionPlan::class, $workplan)
                        && $workplan[SubscriptionPlan::class] === null;
                })
            );

        $this->assertInstanceOf(
            LoadSubscriptionPlan::class,
            ($this->loadSubscriptionPlan)(
                manager: $manager,
                spaceAccount: $spaceAccount,
            )
        );
    }

    public function testInvokeWithValidPlanId(): void
    {
        $plan = $this->createMock(SubscriptionPlan::class);

        $accountData = $this->createMock(AccountData::class);
        $accountData->expects($this->once())
            ->method('visit')
            ->willReturnCallback(function ($field, $promise) use ($accountData) {
                $this->assertEquals('subscriptionPlan', $field);
                $promise->success('premium-plan');
                return $accountData;
            });

        $spaceAccount = new SpaceAccount($this->createMock(Account::class));
        $spaceAccount->accountData = $accountData;

        $this->subscriptionPlanCatalog->expects($this->once())
            ->method('getSubscriptionPlan')
            ->with('premium-plan')
            ->willReturn($plan);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) use ($plan) {
                    return isset($workplan[SubscriptionPlan::class])
                        && $workplan[SubscriptionPlan::class] === $plan;
                })
            );

        $this->assertInstanceOf(
            LoadSubscriptionPlan::class,
            ($this->loadSubscriptionPlan)(
                manager: $manager,
                spaceAccount: $spaceAccount,
            )
        );
    }
}
