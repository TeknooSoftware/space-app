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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Form\Type\Search;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\Space\Infrastructures\Symfony\Form\Type\Search\JobSearchType;

/**
 * Class JobSearchTypeTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(JobSearchType::class)]
class JobSearchTypeTest extends TestCase
{
    private JobSearchType $jobSearchType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobSearchType = new JobSearchType();
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertIsString($this->jobSearchType->getBlockPrefix());
    }

    public function testBuildForm(): void
    {
        $this->assertInstanceOf(
            JobSearchType::class,
            $this->jobSearchType->buildForm(
                $this->createMock(FormBuilderInterface::class),
                [],
            ),
        );
    }

    public function testConfigureOptions(): void
    {
        $this->assertInstanceOf(
            JobSearchType::class,
            $this->jobSearchType->configureOptions(
                $this->createMock(OptionsResolver::class),
            ),
        );
    }
}
