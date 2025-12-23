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
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\Config\SubscriptionPlan;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Recipe\Step\Account\SetQuota;

/**
 * Class SetQuotaTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SetQuota::class)]
class SetQuotaTest extends TestCase
{
    private SetQuota $setQuota;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setQuota = new SetQuota();
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            SetQuota::class,
            ($this->setQuota)(
                new SpaceAccount($this->createStub(Account::class)),
            ),
        );
    }

    public function testInvokeWithPlan(): void
    {
        $account = $this->createMock(Account::class);
        $account->expects($this->once())
            ->method('setQuotas')
            ->with(['cpu' => '1', 'memory' => '128Mi']);

        $plan = $this->createMock(SubscriptionPlan::class);
        $plan->expects($this->once())
            ->method('getQuotas')
            ->willReturn(['cpu' => '1', 'memory' => '128Mi']);

        $this->assertInstanceOf(
            SetQuota::class,
            ($this->setQuota)(
                spaceAccount: new SpaceAccount($account),
                plan: $plan,
            ),
        );
    }

    public function testInvokeWithNullPlan(): void
    {
        $account = $this->createMock(Account::class);
        $account->expects($this->once())
            ->method('setQuotas')
            ->with(null);

        $this->assertInstanceOf(
            SetQuota::class,
            ($this->setQuota)(
                spaceAccount: new SpaceAccount($account),
                plan: null,
            ),
        );
    }
}
