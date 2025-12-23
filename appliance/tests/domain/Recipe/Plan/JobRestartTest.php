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
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Recipe\Plan\JobRestart;
use Teknoo\Space\Recipe\Step\Job\PrepareNewJobForm;
use Teknoo\Space\Recipe\Step\PersistedVariable\LoadPersistedVariablesForJob;

/**
 * Class JobRestartTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(JobRestart::class)]
class JobRestartTest extends TestCase
{
    private JobRestart $jobRestart;

    private RecipeInterface&Stub $recipe;

    private LoadObject&Stub $loadObject;

    private ObjectAccessControlInterface&Stub $objectAccessControl;

    private CreateObject&Stub $createObject;

    private LoadPersistedVariablesForJob&Stub $loadPersistedVariablesForJob;

    private PrepareNewJobForm&Stub $prepareNewJobForm;

    private FormHandlingInterface&Stub $formHandling;

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
        $this->createObject = $this->createStub(CreateObject::class);
        $this->loadPersistedVariablesForJob = $this->createStub(LoadPersistedVariablesForJob::class);
        $this->prepareNewJobForm = $this->createStub(PrepareNewJobForm::class);
        $this->formHandling = $this->createStub(FormHandlingInterface::class);
        $this->renderForm = $this->createStub(RenderFormInterface::class);
        $this->renderError = $this->createStub(RenderError::class);
        $this->defaultErrorTemplate = '42';
        $this->jobRestart = new JobRestart(
            recipe: $this->recipe,
            loadObject: $this->loadObject,
            objectAccessControl: $this->objectAccessControl,
            createObject: $this->createObject,
            loadPersistedVariablesForJob: $this->loadPersistedVariablesForJob,
            prepareNewJobForm: $this->prepareNewJobForm,
            formHandling: $this->formHandling,
            renderForm: $this->renderForm,
            renderError: $this->renderError,
            defaultErrorTemplate: $this->defaultErrorTemplate,
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            JobRestart::class,
            $this->jobRestart,
        );
    }

    public function testPrepare(): void
    {
        $this->assertInstanceOf(
            EditablePlanInterface::class,
            $this->jobRestart->train(
                $this->createStub(ChefInterface::class),
            )
        );
    }
}
