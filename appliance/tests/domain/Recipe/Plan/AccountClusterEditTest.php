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
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - AccountClusterEdit@teknoo.software)
 *
 * @link        https://teknoo.software/applications/space Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
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
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Recipe\Step\JumpIfNot;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Recipe\Plan\AccountClusterEdit;
use Teknoo\Space\Recipe\Step\Account\InjectToView;

/**
 * Class AccountClusterEditTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - AccountClusterEdit@teknoo.software)
 * @license https://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountClusterEdit::class)]
class AccountClusterEditTest extends TestCase
{
    private AccountClusterEdit $AccountClusterEdit;

    private RecipeInterface|MockObject $recipe;

    private JumpIfNot|MockObject $jumpIfNot;

    private LoadObject|MockObject $loadObject;

    private ObjectAccessControlInterface|MockObject $objectAccessControl;

    private FormHandlingInterface|MockObject $formHandling;

    private FormProcessingInterface|MockObject $formProcessing;

    private SaveObject|MockObject $saveObject;

    private InjectToView|MockObject $injectToView;

    private RedirectClientInterface|MockObject $redirectClient;

    private RenderFormInterface|MockObject $renderForm;

    private RenderError|MockObject $renderError;

    private string|Stringable $defaultErrorTemplate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createMock(RecipeInterface::class);
        $this->jumpIfNot = $this->createMock(JumpIfNot::class);
        $this->loadObject = $this->createMock(LoadObject::class);
        $this->objectAccessControl = $this->createMock(ObjectAccessControlInterface::class);
        $this->formHandling = $this->createMock(FormHandlingInterface::class);
        $this->formProcessing = $this->createMock(FormProcessingInterface::class);
        $this->saveObject = $this->createMock(SaveObject::class);
        $this->injectToView = $this->createMock(InjectToView::class);
        $this->redirectClient = $this->createMock(RedirectClientInterface::class);
        $this->renderForm = $this->createMock(RenderFormInterface::class);
        $this->renderError = $this->createMock(RenderError::class);
        $this->defaultErrorTemplate = '42';

        $this->AccountClusterEdit = new AccountClusterEdit(
            recipe: $this->recipe,
            jumpIfNot: $this->jumpIfNot,
            loadObject: $this->loadObject,
            formHandling: $this->formHandling,
            formProcessing: $this->formProcessing,
            saveObject: $this->saveObject,
            injectToView: $this->injectToView,
            renderForm: $this->renderForm,
            renderError: $this->renderError,
            objectAccessControl: $this->objectAccessControl,
            defaultErrorTemplate: $this->defaultErrorTemplate,
        );
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(
            AccountClusterEdit::class,
            $this->AccountClusterEdit,
        );
    }

    public function testPrepare(): void
    {
        self::assertInstanceOf(
            EditablePlanInterface::class,
            $this->AccountClusterEdit->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
