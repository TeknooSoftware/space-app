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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
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
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Recipe\Cookbook\JobRestart;
use Teknoo\Space\Recipe\Step\Job\PrepareNewJobForm;
use Teknoo\Space\Recipe\Step\PersistedVariable\LoadPersistedVariablesForJob;

/**
 * Class JobRestartTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license http://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Recipe\Cookbook\JobRestart
 */
class JobRestartTest extends TestCase
{
    private JobRestart $jobRestart;

    private RecipeInterface|MockObject $recipe;

    private LoadObject|MockObject $loadObject;

    private ObjectAccessControlInterface|MockObject $objectAccessControl;

    private CreateObject|MockObject $createObject;

    private LoadPersistedVariablesForJob|MockObject $loadPersistedVariablesForJob;

    private PrepareNewJobForm|MockObject $prepareNewJobForm;

    private FormHandlingInterface|MockObject $formHandling;

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
        $this->loadPersistedVariablesForJob = $this->createMock(LoadPersistedVariablesForJob::class);
        $this->prepareNewJobForm = $this->createMock(PrepareNewJobForm::class);
        $this->formHandling = $this->createMock(FormHandlingInterface::class);
        $this->renderForm = $this->createMock(RenderFormInterface::class);
        $this->renderError = $this->createMock(RenderError::class);
        $this->defaultErrorTemplate = '42';
        $this->jobRestart = new JobRestart(
            $this->recipe,
            $this->loadObject,
            $this->objectAccessControl,
            $this->createObject,
            $this->loadPersistedVariablesForJob,
            $this->prepareNewJobForm,
            $this->formHandling,
            $this->renderForm,
            $this->renderError,
            $this->defaultErrorTemplate,
        );
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(
            JobRestart::class,
            $this->jobRestart,
        );
    }

    public function testPrepare(): void
    {
        self::assertInstanceOf(
            CookbookInterface::class,
            $this->jobRestart->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
