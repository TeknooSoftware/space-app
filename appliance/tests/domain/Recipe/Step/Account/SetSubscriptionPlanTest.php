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
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/applications/space Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Account;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\Config\SubscriptionPlanCatalog;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Recipe\Step\Account\SetSubscriptionPlan;

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

    private SubscriptionPlanCatalog|MockObject $subscriptionPlanCatalog;

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

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            SetSubscriptionPlan::class,
            ($this->setSubscriptionPlan)(
                $this->createMock(ManagerInterface::class),
                new SpaceAccount($this->createMock(Account::class)),
                'foo',
            ),
        );
    }
}
