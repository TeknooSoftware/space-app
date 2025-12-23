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
use Teknoo\Space\Recipe\Step\Account\SetSubscriptionPlan;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

use function array_key_exists;

/**
 * Class setSubscriptionPlanTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SetSubscriptionPlan::class)]
class SetSubscriptionPlanTest extends TestCase
{
    private SetSubscriptionPlan $setSubscriptionPlan;

    private SubscriptionPlanCatalog&MockObject $subscriptionPlanCatalog;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriptionPlanCatalog = $this->createMock(SubscriptionPlanCatalog::class);

        $this->setSubscriptionPlan = new SetSubscriptionPlan(
            $this->subscriptionPlanCatalog,
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            SetSubscriptionPlan::class,
            ($this->setSubscriptionPlan)(
                $this->createStub(ManagerInterface::class),
                new SpaceAccount($this->createStub(Account::class)),
                'foo',
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInvokeThrowsExceptionWhenAccountDataIsNull(): void
    {
        $spaceAccount = new SpaceAccount($this->createStub(Account::class));
        $spaceAccount->accountData = null;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Missing Space Account data');

        ($this->setSubscriptionPlan)(
            manager: $this->createStub(ManagerInterface::class),
            spaceAccount: $spaceAccount,
        );
    }

    public function testInvokeWithSubscriptionPlanId(): void
    {
        $accountData = $this->createMock(AccountData::class);
        $accountData->expects($this->once())
            ->method('setSubscriptionPlan')
            ->with('plan-123');

        $accountData->expects($this->once())
            ->method('visit')
            ->willReturnCallback(function ($field, $promise) use ($accountData) {
                $this->assertEquals('subscriptionPlan', $field);
                $promise->success('plan-123');
                return $accountData;
            });

        $plan = $this->createStub(SubscriptionPlan::class);
        $this->subscriptionPlanCatalog->expects($this->once())
            ->method('getSubscriptionPlan')
            ->with('plan-123')
            ->willReturn($plan);

        $spaceAccount = new SpaceAccount($this->createStub(Account::class));
        $spaceAccount->accountData = $accountData;

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
            SetSubscriptionPlan::class,
            ($this->setSubscriptionPlan)(
                manager: $manager,
                spaceAccount: $spaceAccount,
                subscriptionPlanId: 'plan-123',
            ),
        );
    }

    public function testInvokeWithoutSubscriptionPlanId(): void
    {
        $accountData = $this->createMock(AccountData::class);
        $accountData->expects($this->never())
            ->method('setSubscriptionPlan');

        $accountData->expects($this->once())
            ->method('visit')
            ->willReturnCallback(function ($field, $promise) use ($accountData) {
                $this->assertEquals('subscriptionPlan', $field);
                $promise->success('existing-plan');
                return $accountData;
            });

        $plan = $this->createStub(SubscriptionPlan::class);
        $this->subscriptionPlanCatalog->expects($this->once())
            ->method('getSubscriptionPlan')
            ->with('existing-plan')
            ->willReturn($plan);

        $spaceAccount = new SpaceAccount($this->createStub(Account::class));
        $spaceAccount->accountData = $accountData;

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan');

        $this->assertInstanceOf(
            SetSubscriptionPlan::class,
            ($this->setSubscriptionPlan)(
                manager: $manager,
                spaceAccount: $spaceAccount,
                subscriptionPlanId: null,
            ),
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

        $this->subscriptionPlanCatalog->expects($this->never())
            ->method('getSubscriptionPlan');

        $spaceAccount = new SpaceAccount($this->createStub(Account::class));
        $spaceAccount->accountData = $accountData;

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return array_key_exists(SubscriptionPlan::class, $workplan)
                        && null === $workplan[SubscriptionPlan::class];
                })
            );

        $this->assertInstanceOf(
            SetSubscriptionPlan::class,
            ($this->setSubscriptionPlan)(
                manager: $manager,
                spaceAccount: $spaceAccount,
            ),
        );
    }
}
