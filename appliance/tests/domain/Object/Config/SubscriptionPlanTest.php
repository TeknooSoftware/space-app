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
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Object\Config;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Paas\Object\AccountQuota;
use Teknoo\Space\Object\Config\SubscriptionPlan;

/**
 * Class SubscriptionPlan.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SubscriptionPlan::class)]
class SubscriptionPlanTest extends TestCase
{
    private SubscriptionPlan $plan;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->plan = new SubscriptionPlan(
            id: 'foo',
            name: 'Foo',
            quotas: [
                [
                    'category' => 'compute',
                    'type' => 'cpu',
                    'capacity' => '5',
                    'require' => '2',
                ]
            ]
        );
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(
            AccountQuota::class,
            $this->plan->getQuotas()['cpu'],
        );
    }
}
