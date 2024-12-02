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
 * @link        https://teknoo.software/applications/space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Infrastructures\Doctrine\Form\UserData;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\East\Common\Doctrine\Writer\ODM\MediaWriter;
use Teknoo\Space\Infrastructures\Doctrine\Form\UserData\UserDataType;

/**
 * Class UserDataTypeTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(UserDataType::class)]
class UserDataTypeTest extends TestCase
{
    private UserDataType $userDataType;

    private MediaWriter|MockObject $mediaWriter;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mediaWriter = $this->createMock(MediaWriter::class);

        $this->userDataType = new UserDataType(
            $this->mediaWriter,
        );
    }

    public function testBuildForm(): void
    {
        self::assertInstanceOf(
            UserDataType::class,
            $this->userDataType->buildForm(
                $this->createMock(FormBuilderInterface::class),
                ['environmentsList' => ['bar']],
            ),
        );
    }

    public function testConfigureOptions(): void
    {
        self::assertInstanceOf(
            UserDataType::class,
            $this->userDataType->configureOptions(
                $this->createMock(OptionsResolver::class),
            ),
        );
    }
}
