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
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\JumpIf;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Contracts\Recipe\Step\Job\CallNewJobInterface;
use Teknoo\Space\Contracts\Recipe\Step\Job\NewJobNotifierInterface;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Job\PersistJobVar;
use Teknoo\Space\Recipe\Plan\JobStart;
use Teknoo\Space\Recipe\Step\AccountCluster\LoadAccountClusters;
use Teknoo\Space\Recipe\Step\Job\PrepareNewJobForm;
use Teknoo\Space\Recipe\Step\NewJob\NewJobSetDefaults;
use Teknoo\Space\Recipe\Step\PersistedVariable\LoadPersistedVariablesForJob;
use Teknoo\Space\Recipe\Step\Project\LoadAccountFromProject;

/**
 * Class JobStartTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license https://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(JobStart::class)]
class JobStartTest extends TestCase
{
    private JobStart $jobStart;

    private RecipeInterface|MockObject $recipe;

    private LoadObject|MockObject $loadObject;

    private ObjectAccessControlInterface|MockObject $objectAccessControl;

    private LoadAccountFromProject|MockObject $loadAccountFromProject;

    private CreateObject|MockObject $createObject;

    private PrepareNewJobForm|MockObject $prepareNewJobForm;

    private LoadPersistedVariablesForJob|MockObject $loadPersistedVariablesForJob;

    private LoadAccountClusters|MockObject $loadAccountClusters;

    private FormHandlingInterface|MockObject $formHandling;

    private FormProcessingInterface|MockObject $formProcessing;

    private NewJobSetDefaults|MockObject $newJobSetDefaults;

    private NewJobNotifierInterface|MockObject $newJobNotifier;


    private JumpIf|MockObject $jumpIf;

    private CallNewJobInterface|MockObject $callNewJob;

    private PersistJobVar|MockObject $persistJobVar;

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
        $this->loadObject = $this->createMock(LoadObject::class);
        $this->objectAccessControl = $this->createMock(ObjectAccessControlInterface::class);
        $this->loadAccountFromProject = $this->createMock(LoadAccountFromProject::class);
        $this->createObject = $this->createMock(CreateObject::class);
        $this->prepareNewJobForm = $this->createMock(PrepareNewJobForm::class);
        $this->loadPersistedVariablesForJob = $this->createMock(LoadPersistedVariablesForJob::class);
        $this->loadAccountClusters = $this->createMock(LoadAccountClusters::class);
        $this->formHandling = $this->createMock(FormHandlingInterface::class);
        $this->formProcessing = $this->createMock(FormProcessingInterface::class);
        $this->newJobSetDefaults = $this->createMock(NewJobSetDefaults::class);
        $this->newJobNotifier = $this->createMock(NewJobNotifierInterface::class);
        $this->jumpIf = $this->createMock(JumpIf::class);
        $this->callNewJob = $this->createMock(CallNewJobInterface::class);
        $this->persistJobVar = $this->createMock(PersistJobVar::class);
        $this->redirectClient = $this->createMock(RedirectClientInterface::class);
        $this->renderForm = $this->createMock(RenderFormInterface::class);
        $this->renderError = $this->createMock(RenderError::class);
        $this->defaultErrorTemplate = '42';
        $this->jobStart = new JobStart(
            recipe: $this->recipe,
            loadObject: $this->loadObject,
            objectAccessControl: $this->objectAccessControl,
            loadAccountFromProject: $this->loadAccountFromProject,
            createObject: $this->createObject,
            prepareNewJobForm: $this->prepareNewJobForm,
            loadPersistedVariablesForJob: $this->loadPersistedVariablesForJob,
            loadAccountClusters: $this->loadAccountClusters,
            formHandling: $this->formHandling,
            formProcessing: $this->formProcessing,
            newJobSetDefaults: $this->newJobSetDefaults,
            persistJobVar: $this->persistJobVar,
            newJobNotifier: $this->newJobNotifier,
            jumpIf: $this->jumpIf,
            callNewJob: $this->callNewJob,
            redirectClient: $this->redirectClient,
            renderForm: $this->renderForm,
            renderError: $this->renderError,
            defaultErrorTemplate: $this->defaultErrorTemplate
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
            EditablePlanInterface::class,
            $this->jobStart->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
