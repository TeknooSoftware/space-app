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
use PHPUnit\Framework\MockObject\Stub;
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
use Teknoo\Space\Recipe\Plan\UserCreateFromFormJwtToken;

/**
 * Class UserGetJwtTokenTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(UserCreateFromFormJwtToken::class)]
class UserCreateFromFormJwtTokenTest extends TestCase
{
    private UserCreateFromFormJwtToken $userGetJwtToken;

    private RecipeInterface&Stub $recipe;

    private CreateObject&Stub $createObject;

    private FormHandlingInterface&Stub $formHandling;

    private FormProcessingInterface&Stub $formProcessing;

    private JwtCreateTokenInterface&Stub $jwtCreateToken;

    private Stop&Stub $stop;

    private Render&Stub $render;

    private RenderFormInterface&Stub $renderForm;

    private RenderError&Stub $renderError;

    private string|Stringable $defaultErrorTemplate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createStub(RecipeInterface::class);
        $this->createObject = $this->createStub(CreateObject::class);
        $this->formHandling = $this->createStub(FormHandlingInterface::class);
        $this->formProcessing = $this->createStub(FormProcessingInterface::class);
        $this->jwtCreateToken = $this->createStub(JwtCreateTokenInterface::class);
        $this->stop = $this->createStub(Stop::class);
        $this->render = $this->createStub(Render::class);
        $this->renderForm = $this->createStub(RenderFormInterface::class);
        $this->renderError = $this->createStub(RenderError::class);
        $this->defaultErrorTemplate = '42';

        $this->userGetJwtToken = new UserCreateFromFormJwtToken(
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
            UserCreateFromFormJwtToken::class,
            $this->userGetJwtToken,
        );
    }

    public function testPrepare(): void
    {
        $this->assertInstanceOf(
            EditablePlanInterface::class,
            $this->userGetJwtToken->train(
                $this->createStub(ChefInterface::class),
            )
        );
    }
}
