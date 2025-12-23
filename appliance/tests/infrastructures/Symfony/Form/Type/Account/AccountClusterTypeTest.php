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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Form\Type\Account;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\Space\Infrastructures\Symfony\Form\Type\Account\AccountClusterType;

/**
 * Class AccountClusterTypeTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountClusterType::class)]
class AccountClusterTypeTest extends TestCase
{
    private AccountClusterType $accountClusterType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->accountClusterType = new AccountClusterType();
    }

    public function testBuildForm(): void
    {
        $this->accountClusterType->buildForm(
            $this->createStub(FormBuilderInterface::class),
            [],
        );
        $this->assertTrue(true);
    }

    public function testConfigureOptions(): void
    {
        $this->accountClusterType->configureOptions(
            $this->createStub(OptionsResolver::class),
        );
        $this->assertTrue(true);
    }
}
