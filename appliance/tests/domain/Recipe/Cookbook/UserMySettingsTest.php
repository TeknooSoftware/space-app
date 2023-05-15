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

namespace Teknoo\Space\Tests\Unit\Recipe\Cookbook;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Recipe\Cookbook\UserMySettings;

/**
 * Class UserMySettingsTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license http://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Recipe\Cookbook\UserMySettings
 * @covers \Teknoo\Space\Recipe\Cookbook\Traits\EditOwnsSettingsTrait
 */
class UserMySettingsTest extends TestCase
{
    private UserMySettings $userMySettings;

    private RecipeInterface|MockObject $recipe;

    private FormHandlingInterface|MockObject $formHandling;

    private FormProcessingInterface|MockObject $formProcessing;

    private SaveObject|MockObject $saveObject;

    private RenderFormInterface|MockObject $renderForm;

    private RenderError|MockObject $renderError;

    private string $objectClass;

    private string $defaultErrorTemplate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createMock(RecipeInterface::class);
        $this->formHandling = $this->createMock(FormHandlingInterface::class);
        $this->formProcessing = $this->createMock(FormProcessingInterface::class);
        $this->saveObject = $this->createMock(SaveObject::class);
        $this->renderForm = $this->createMock(RenderFormInterface::class);
        $this->renderError = $this->createMock(RenderError::class);
        $this->objectClass = SpaceAccount::class;
        $this->defaultErrorTemplate = '42';

        $this->userMySettings = new UserMySettings(
            $this->recipe,
            $this->formHandling,
            $this->formProcessing,
            $this->saveObject,
            $this->renderForm,
            $this->renderError,
            $this->objectClass,
            $this->defaultErrorTemplate
        );
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(
            UserMySettings::class,
            $this->userMySettings,
        );
    }

    public function testPrepare(): void
    {
        self::assertInstanceOf(
            CookbookInterface::class,
            $this->userMySettings->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
