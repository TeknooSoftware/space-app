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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Recipe\Step\Account;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Account\PrepareForm;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\SubscriptionPlanCatalog;
use Teknoo\Space\Object\DTO\SpaceAccount;
use RuntimeException;
use Teknoo\Space\Object\Config\SubscriptionPlan;
use Teknoo\Space\Object\Persisted\AccountData;

/**
 * Class PrepareFormTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(PrepareForm::class)]
class PrepareFormTest extends TestCase
{
    private PrepareForm $prepareForm;

    private SubscriptionPlanCatalog&MockObject $subscriptionPlanCatalog;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriptionPlanCatalog = $this->createMock(SubscriptionPlanCatalog::class);

        $this->prepareForm = new PrepareForm(
            $this->subscriptionPlanCatalog,
        );
    }

    public function testInvoke(): void
    {
        $this->subscriptionPlanCatalog
            ->expects($this->never())
            ->method('getSubscriptionPlan');

        $this->assertInstanceOf(
            PrepareForm::class,
            ($this->prepareForm)(
                $this->createStub(ManagerInterface::class),
                $this->createStub(ClusterCatalog::class),
                new SpaceAccount(
                    account: $this->createStub(Account::class),
                    environments: []
                ),
                [],
            )
        );
    }

    public function testInvokeWithoutSpaceAccount(): void
    {
        $this->subscriptionPlanCatalog
            ->expects($this->never())
            ->method('getSubscriptionPlan');

        $this->expectException(RuntimeException::class);
        ($this->prepareForm)(
            $this->createStub(ManagerInterface::class),
            new ClusterCatalog([], []),
            null,
        );
    }

    public function testInvokeWithoutSubscriptionPlan(): void
    {
        $this->subscriptionPlanCatalog
            ->expects($this->never())
            ->method('getSubscriptionPlan');

        $account = new Account();
        $clusterCatalog = new ClusterCatalog([], []);

        $manager = $this->createMock(ManagerInterface::class);
        $manager
            ->expects($this->once())
            ->method('updateWorkPlan')
            ->with([
                'formOptions' => [
                    'foo' => 'bar',
                    'subscriptionPlan' => null,
                    'clusterCatalog' => $clusterCatalog,
                ],
            ])
            ->willReturnSelf();

        $this->assertInstanceOf(
            PrepareForm::class,
            ($this->prepareForm)(
                $manager,
                $clusterCatalog,
                new SpaceAccount(
                    account: $account,
                    accountData: new AccountData(account: $account, subscriptionPlan: ''),
                ),
                ['foo' => 'bar'],
            )
        );
    }

    public function testInvokeWithSubscriptionPlan(): void
    {
        $plan = new SubscriptionPlan('plan-id', 'Plan', []);
        $this->subscriptionPlanCatalog
            ->expects($this->once())
            ->method('getSubscriptionPlan')
            ->with('plan-id')
            ->willReturn($plan);

        $account = new Account();
        $clusterCatalog = new ClusterCatalog([], []);

        $manager = $this->createMock(ManagerInterface::class);
        $manager
            ->expects($this->once())
            ->method('updateWorkPlan')
            ->with([
                'formOptions' => [
                    'subscriptionPlan' => $plan,
                    'clusterCatalog' => $clusterCatalog,
                ],
            ])
            ->willReturnSelf();

        $this->assertInstanceOf(
            PrepareForm::class,
            ($this->prepareForm)(
                $manager,
                $clusterCatalog,
                new SpaceAccount(
                    account: $account,
                    accountData: new AccountData(account: $account, subscriptionPlan: 'plan-id'),
                ),
            )
        );
    }
}
