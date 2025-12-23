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
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Recipe\Plan\JobGet;
use Teknoo\Space\Recipe\Step\Job\ExtractProject;
use Teknoo\Space\Recipe\Step\ProjectMetadata\InjectToViewMetadata;
use Teknoo\Space\Recipe\Step\ProjectMetadata\LoadProjectMetadata;

/**
 * Class JobGetTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(JobGet::class)]
class JobGetTest extends TestCase
{
    private JobGet $jobGet;

    private RecipeInterface&Stub $recipe;

    private LoadObject&Stub $loadObject;

    private ExtractProject&Stub $extractProject;

    private LoadProjectMetadata&Stub $loadProjectMetadata;

    private InjectToViewMetadata&Stub $injectToViewMetadata;

    private Render&Stub $render;

    private RenderError&Stub $renderError;

    private ObjectAccessControlInterface&Stub $objectAccessControl;

    private string|Stringable $defaultErrorTemplate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createStub(RecipeInterface::class);
        $this->loadObject = $this->createStub(LoadObject::class);
        $this->extractProject = $this->createStub(ExtractProject::class);
        $this->loadProjectMetadata = $this->createStub(LoadProjectMetadata::class);
        $this->injectToViewMetadata = $this->createStub(InjectToViewMetadata::class);
        $this->render = $this->createStub(Render::class);
        $this->renderError = $this->createStub(RenderError::class);
        $this->objectAccessControl = $this->createStub(ObjectAccessControlInterface::class);
        $this->defaultErrorTemplate = '42';
        $this->jobGet = new JobGet(
            recipe: $this->recipe,
            loadObject: $this->loadObject,
            extractProject: $this->extractProject,
            loadProjectMetadata: $this->loadProjectMetadata,
            injectToViewMetadata: $this->injectToViewMetadata,
            render: $this->render,
            renderError: $this->renderError,
            objectAccessControl: $this->objectAccessControl,
            defaultErrorTemplate: $this->defaultErrorTemplate,
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            JobGet::class,
            $this->jobGet,
        );
    }

    public function testPrepare(): void
    {
        $this->assertInstanceOf(
            EditablePlanInterface::class,
            $this->jobGet->train(
                $this->createStub(ChefInterface::class),
            )
        );
    }
}
