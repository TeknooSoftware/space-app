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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Form\Type\Job;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\Space\Infrastructures\Symfony\Form\Type\Job\NewJobType;

/**
 * Class NewJobTypeTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(NewJobType::class)]
class NewJobTypeTest extends TestCase
{
    private NewJobType $newJobType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();


        $this->newJobType = new NewJobType();
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertIsString($this->newJobType->getBlockPrefix());
    }

    public function testBuildForm(): void
    {
        $this->newJobType->buildForm(
            $this->createStub(FormBuilderInterface::class),
            ['foo' => 'bar', 'environmentsList' => ['prod']],
        );
        $this->assertTrue(true);
    }

    public function testConfigureOptions(): void
    {
        $this->newJobType->configureOptions(
            $this->createStub(OptionsResolver::class),
        );
        $this->assertTrue(true);
    }
}
