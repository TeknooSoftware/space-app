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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Form\Type\Project;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\Space\Infrastructures\Symfony\Form\Type\Project\VarsType;

/**
 * Class VarsTypeTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(VarsType::class)]
class VarsTypeTest extends TestCase
{
    private VarsType $varsType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->varsType = new VarsType();
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertIsString($this->varsType->getBlockPrefix());
    }

    public function testBuildForm(): void
    {
        $this->assertInstanceOf(
            VarsType::class,
            $this->varsType->buildForm(
                $this->createMock(FormBuilderInterface::class),
                ['environmentsList' => ['bar']],
            ),
        );
    }

    public function testConfigureOptions(): void
    {
        $this->assertInstanceOf(
            VarsType::class,
            $this->varsType->configureOptions(
                $this->createMock(OptionsResolver::class),
            ),
        );
    }
}
