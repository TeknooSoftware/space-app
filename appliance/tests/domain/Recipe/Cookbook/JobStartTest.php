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
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\JumpIf;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Contracts\Recipe\Step\Job\CallNewJobInterface;
use Teknoo\Space\Contracts\Recipe\Step\Job\NewJobNotifierInterface;
use Teknoo\Space\Recipe\Cookbook\JobStart;
use Teknoo\Space\Recipe\Step\Job\PrepareNewJobForm;
use Teknoo\Space\Recipe\Step\PersistedVariable\LoadPersistedVariablesForJob;

/**
 * Class JobStartTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license http://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Recipe\Cookbook\JobStart
 */
class JobStartTest extends TestCase
{
    private JobStart $jobStart;

    private RecipeInterface|MockObject $recipe;

    private LoadObject|MockObject $loadObject;

    private ObjectAccessControlInterface|MockObject $objectAccessControl;

    private CreateObject|MockObject $createObject;

    private PrepareNewJobForm|MockObject $prepareNewJobForm;

    private LoadPersistedVariablesForJob|MockObject $loadPersistedVariablesForJob;

    private FormHandlingInterface|MockObject $formHandling;

    private FormProcessingInterface|MockObject $formProcessing;

    private NewJobNotifierInterface|MockObject $newJobNotifier;


    private JumpIf|MockObject $jumpIf;

    private CallNewJobInterface|MockObject $callNewJob;

    private RedirectClientInterface|MockObject $redirectClient;

    private RenderFormInterface|MockObject $renderForm;

    private RenderError|MockObject $renderError;

    private string $defaultErrorTemplate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createMock(RecipeInterface::class);
        $this->loadObject = $this->createMock(LoadObject::class);
        $this->objectAccessControl = $this->createMock(ObjectAccessControlInterface::class);
        $this->createObject = $this->createMock(CreateObject::class);
        $this->prepareNewJobForm = $this->createMock(PrepareNewJobForm::class);
        $this->loadPersistedVariablesForJob = $this->createMock(LoadPersistedVariablesForJob::class);
        $this->formHandling = $this->createMock(FormHandlingInterface::class);
        $this->formProcessing = $this->createMock(FormProcessingInterface::class);
        $this->newJobNotifier = $this->createMock(NewJobNotifierInterface::class);
        $this->jumpIf = $this->createMock(JumpIf::class);
        $this->callNewJob = $this->createMock(CallNewJobInterface::class);
        $this->redirectClient = $this->createMock(RedirectClientInterface::class);
        $this->renderForm = $this->createMock(RenderFormInterface::class);
        $this->renderError = $this->createMock(RenderError::class);
        $this->defaultErrorTemplate = '42';
        $this->jobStart = new JobStart(
            $this->recipe,
            $this->loadObject,
            $this->objectAccessControl,
            $this->createObject,
            $this->prepareNewJobForm,
            $this->loadPersistedVariablesForJob,
            $this->formHandling,
            $this->formProcessing,
            $this->newJobNotifier,
            $this->jumpIf,
            $this->callNewJob,
            $this->redirectClient,
            $this->renderForm,
            $this->renderError,
            $this->defaultErrorTemplate
        );
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(
            JobStart::class,
            $this->jobStart,
        );
    }

    public function testPrepare(): void
    {
        self::assertInstanceOf(
            CookbookInterface::class,
            $this->jobStart->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
