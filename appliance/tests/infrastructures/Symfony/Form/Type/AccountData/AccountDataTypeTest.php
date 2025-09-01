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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Form\Type\AccountData;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\Space\Infrastructures\Symfony\Form\Type\AccountData\AccountDataType;
use Teknoo\Space\Object\Config\SubscriptionPlanCatalog;

/**
 * Class AccountDataTypeTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountDataType::class)]
class AccountDataTypeTest extends TestCase
{
    private AccountDataType $accountDataType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->accountDataType = new AccountDataType($this->createMock(SubscriptionPlanCatalog::class));
    }

    public function testBuildForm(): void
    {
        $this->assertInstanceOf(
            AccountDataType::class,
            $this->accountDataType->buildForm(
                $this->createMock(FormBuilderInterface::class),
                [],
            ),
        );
    }

    public function testConfigureOptions(): void
    {
        $this->assertInstanceOf(
            AccountDataType::class,
            $this->accountDataType->configureOptions(
                $this->createMock(OptionsResolver::class),
            ),
        );
    }
}
