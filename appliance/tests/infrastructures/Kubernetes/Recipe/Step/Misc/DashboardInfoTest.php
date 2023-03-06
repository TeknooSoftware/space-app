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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Infrastructures\Kubernetes\Recipe\Step\Misc;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Misc\DashboardInfo;

/**
 * Class DashboardInfoTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Misc\DashboardInfo
 */
class DashboardInfoTest extends TestCase
{
    private DashboardInfo $dashboardInfo;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->dashboardInfo = new DashboardInfo();
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            DashboardInfo::class,
            ($this->dashboardInfo)(
                $this->createMock(ManagerInterface::class),
                $this->createMock(ParametersBag::class),
                $this->createMock(Account::class),
            )
        );
    }
}
