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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Recipe\Step\Account;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Account\PrepareForm;
use Teknoo\Space\Object\Config\SubscriptionPlanCatalog;
use Teknoo\Space\Object\DTO\SpaceAccount;

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

    private SubscriptionPlanCatalog|MockObject $subscriptionPlanCatalog;

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
        self::assertInstanceOf(
            PrepareForm::class,
            ($this->prepareForm)(
                $this->createMock(ManagerInterface::class),
                new SpaceAccount(
                    account: $this->createMock(Account::class),
                    environmentResumes: []
                ),
                [],
            )
        );
    }
}
