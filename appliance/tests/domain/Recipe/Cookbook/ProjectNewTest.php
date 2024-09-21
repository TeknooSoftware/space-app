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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Recipe\Cookbook\ProjectNew;

/**
 * Class ProjectNewTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license http://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ProjectNew::class)]
class ProjectNewTest extends TestCase
{
    private ProjectNew $projectNew;

    private RecipeInterface|MockObject $recipe;

    private ObjectAccessControlInterface|MockObject $objectAccessControl;

    private CreateObject|MockObject $createObject;

    private FormHandlingInterface|MockObject $formHandling;

    private FormProcessingInterface|MockObject $formProcessing;

    private SaveObject|MockObject $saveObject;

    private RedirectClientInterface|MockObject $redirectClient;

    private RenderFormInterface|MockObject $renderForm;

    private RenderError|MockObject $renderError;

    private iterable $additionalSteps;

    private string $defaultErrorTemplate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createMock(RecipeInterface::class);
        $this->objectAccessControl = $this->createMock(ObjectAccessControlInterface::class);
        $this->createObject = $this->createMock(CreateObject::class);
        $this->formHandling = $this->createMock(FormHandlingInterface::class);
        $this->formProcessing = $this->createMock(FormProcessingInterface::class);
        $this->saveObject = $this->createMock(SaveObject::class);
        $this->redirectClient = $this->createMock(RedirectClientInterface::class);
        $this->renderForm = $this->createMock(RenderFormInterface::class);
        $this->renderError = $this->createMock(RenderError::class);
        $this->additionalSteps = [];
        $this->defaultErrorTemplate = '42';
        $this->projectNew = new ProjectNew(
            $this->recipe,
            $this->objectAccessControl,
            $this->createObject,
            $this->formHandling,
            $this->formProcessing,
            $this->saveObject,
            $this->redirectClient,
            $this->renderForm,
            $this->renderError,
            $this->additionalSteps,
            $this->defaultErrorTemplate,
        );
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(
            ProjectNew::class,
            $this->projectNew,
        );
    }

    public function testPrepare(): void
    {
        self::assertInstanceOf(
            CookbookInterface::class,
            $this->projectNew->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
