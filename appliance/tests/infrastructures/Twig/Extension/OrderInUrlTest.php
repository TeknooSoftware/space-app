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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Twig\Extension;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Space\Infrastructures\Twig\Extension\OrderInUrl;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(OrderInUrl::class)]
class OrderInUrlTest extends TestCase
{
    private OrderInUrl $orderInUrl;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->orderInUrl = new OrderInUrl();
    }

    public function testGetFunctions()
    {
        self::assertIsArray($this->orderInUrl->getFunctions());
    }

    public function testGetName()
    {
        self::assertEquals('app_order_in_url', $this->orderInUrl->getName());
    }

    public function testOrderInUrl()
    {
        self::assertIsString(
            $this->orderInUrl->orderInUrl(
                ['order' => 'bar', 'direction' => 'DESC'],
                'foo'
            )
        );
    }
}
