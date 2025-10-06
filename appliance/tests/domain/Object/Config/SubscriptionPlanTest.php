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
        $this->assertInstanceOf(
            AccountQuota::class,
            $this->plan->getQuotas()['cpu'],
        );
    }

    public function testGetClustersWithDefaultEmptyArray(): void
    {
        $this->assertSame([], $this->plan->getClusters());
    }

    public function testConstructWithStringCluster(): void
    {
        $plan = new SubscriptionPlan(
            id: 'foo',
            name: 'Foo',
            quotas: [
                [
                    'category' => 'compute',
                    'type' => 'cpu',
                    'capacity' => '5',
                    'require' => '2',
                ]
            ],
            clusters: 'cluster1'
        );

        $this->assertSame(['cluster1'], $plan->getClusters());
    }

    public function testConstructWithArrayClusters(): void
    {
        $plan = new SubscriptionPlan(
            id: 'foo',
            name: 'Foo',
            quotas: [
                [
                    'category' => 'compute',
                    'type' => 'cpu',
                    'capacity' => '5',
                    'require' => '2',
                ]
            ],
            clusters: ['cluster1', 'cluster2']
        );

        $this->assertSame(['cluster1', 'cluster2'], $plan->getClusters());
    }

    public function testConstructWithCustomCountsAllowed(): void
    {
        $plan = new SubscriptionPlan(
            id: 'foo',
            name: 'Foo',
            quotas: [
                [
                    'category' => 'compute',
                    'type' => 'cpu',
                    'capacity' => '5',
                    'require' => '2',
                ]
            ],
            envsCountAllowed: 5,
            projectsCountAllowed: 10,
            clusters: []
        );

        $this->assertSame(5, $plan->envsCountAllowed);
        $this->assertSame(10, $plan->projectsCountAllowed);
    }
}
