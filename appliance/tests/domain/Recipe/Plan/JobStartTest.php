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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(JobStart::class)]
class JobStartTest extends TestCase
{
    private JobStart $jobStart;

    private RecipeInterface&Stub $recipe;

    private LoadObject&Stub $loadObject;

    private ObjectAccessControlInterface&Stub $objectAccessControl;

    private LoadAccountFromProject&Stub $loadAccountFromProject;

    private CreateObject&Stub $createObject;

    private PrepareNewJobForm&Stub $prepareNewJobForm;

    private LoadPersistedVariablesForJob&Stub $loadPersistedVariablesForJob;

    private LoadAccountClusters&Stub $loadAccountClusters;

    private FormHandlingInterface&Stub $formHandling;

    private FormProcessingInterface&Stub $formProcessing;

    private NewJobSetDefaults&Stub $newJobSetDefaults;

    private NewJobNotifierInterface&Stub $newJobNotifier;


    private JumpIf&Stub $jumpIf;

    private CallNewJobInterface&Stub $callNewJob;

    private PersistJobVar&Stub $persistJobVar;

    private RedirectClientInterface&Stub $redirectClient;

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
        $this->loadObject = $this->createStub(LoadObject::class);
        $this->objectAccessControl = $this->createStub(ObjectAccessControlInterface::class);
        $this->loadAccountFromProject = $this->createStub(LoadAccountFromProject::class);
        $this->createObject = $this->createStub(CreateObject::class);
        $this->prepareNewJobForm = $this->createStub(PrepareNewJobForm::class);
        $this->loadPersistedVariablesForJob = $this->createStub(LoadPersistedVariablesForJob::class);
        $this->loadAccountClusters = $this->createStub(LoadAccountClusters::class);
        $this->formHandling = $this->createStub(FormHandlingInterface::class);
        $this->formProcessing = $this->createStub(FormProcessingInterface::class);
        $this->newJobSetDefaults = $this->createStub(NewJobSetDefaults::class);
        $this->newJobNotifier = $this->createStub(NewJobNotifierInterface::class);
        $this->jumpIf = $this->createStub(JumpIf::class);
        $this->callNewJob = $this->createStub(CallNewJobInterface::class);
        $this->persistJobVar = $this->createStub(PersistJobVar::class);
        $this->redirectClient = $this->createStub(RedirectClientInterface::class);
        $this->renderForm = $this->createStub(RenderFormInterface::class);
        $this->renderError = $this->createStub(RenderError::class);
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
        $this->assertInstanceOf(
            JobStart::class,
            $this->jobStart,
        );
    }

    public function testPrepare(): void
    {
        $this->assertInstanceOf(
            EditablePlanInterface::class,
            $this->jobStart->train(
                $this->createStub(ChefInterface::class),
            )
        );
    }
}
