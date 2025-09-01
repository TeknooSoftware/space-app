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

namespace Teknoo\Space\Tests\Unit\Recipe\Plan;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Stringable;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\Stop;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Contracts\Recipe\Step\User\JwtCreateTokenInterface;
use Teknoo\Space\Recipe\Plan\UserGetJwtToken;

/**
 * Class UserGetJwtTokenTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(UserGetJwtToken::class)]
class UserGetJwtTokenTest extends TestCase
{
    private UserGetJwtToken $userGetJwtToken;

    private RecipeInterface&MockObject $recipe;

    private CreateObject&MockObject $createObject;

    private FormHandlingInterface&MockObject $formHandling;

    private FormProcessingInterface&MockObject $formProcessing;

    private JwtCreateTokenInterface&MockObject $jwtCreateToken;

    private Stop&MockObject $stop;

    private Render&MockObject $render;

    private RenderFormInterface&MockObject $renderForm;

    private RenderError&MockObject $renderError;

    private string|Stringable $defaultErrorTemplate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createMock(RecipeInterface::class);
        $this->createObject = $this->createMock(CreateObject::class);
        $this->formHandling = $this->createMock(FormHandlingInterface::class);
        $this->formProcessing = $this->createMock(FormProcessingInterface::class);
        $this->jwtCreateToken = $this->createMock(JwtCreateTokenInterface::class);
        $this->stop = $this->createMock(Stop::class);
        $this->render = $this->createMock(Render::class);
        $this->renderForm = $this->createMock(RenderFormInterface::class);
        $this->renderError = $this->createMock(RenderError::class);
        $this->defaultErrorTemplate = '42';

        $this->userGetJwtToken = new UserGetJwtToken(
            recipe: $this->recipe,
            createObject: $this->createObject,
            formHandling: $this->formHandling,
            formProcessing: $this->formProcessing,
            jwtCreateToken: $this->jwtCreateToken,
            render: $this->render,
            stop: $this->stop,
            renderForm: $this->renderForm,
            renderError: $this->renderError,
            defaultErrorTemplate: $this->defaultErrorTemplate
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            UserGetJwtToken::class,
            $this->userGetJwtToken,
        );
    }

    public function testPrepare(): void
    {
        $this->assertInstanceOf(
            EditablePlanInterface::class,
            $this->userGetJwtToken->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
