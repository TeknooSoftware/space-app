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
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Recipe\Cookbook\JobGet;
use Teknoo\Space\Recipe\Step\Job\ExtractProject;
use Teknoo\Space\Recipe\Step\ProjectMetadata\InjectToViewMetadata;
use Teknoo\Space\Recipe\Step\ProjectMetadata\LoadProjectMetadata;

/**
 * Class JobGetTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license http://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Recipe\Cookbook\JobGet
 */
class JobGetTest extends TestCase
{
    private JobGet $jobGet;

    private RecipeInterface|MockObject $recipe;

    private LoadObject|MockObject $loadObject;

    private ExtractProject|MockObject $extractProject;

    private LoadProjectMetadata|MockObject $loadProjectMetadata;

    private InjectToViewMetadata|MockObject $injectToViewMetadata;

    private Render|MockObject $render;

    private RenderError|MockObject $renderError;

    private ObjectAccessControlInterface|MockObject $objectAccessControl;

    private string $defaultErrorTemplate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createMock(RecipeInterface::class);
        $this->loadObject = $this->createMock(LoadObject::class);
        $this->extractProject = $this->createMock(ExtractProject::class);
        $this->loadProjectMetadata = $this->createMock(LoadProjectMetadata::class);
        $this->injectToViewMetadata = $this->createMock(InjectToViewMetadata::class);
        $this->render = $this->createMock(Render::class);
        $this->renderError = $this->createMock(RenderError::class);
        $this->objectAccessControl = $this->createMock(ObjectAccessControlInterface::class);
        $this->defaultErrorTemplate = '42';
        $this->jobGet = new JobGet(
            $this->recipe,
            $this->loadObject,
            $this->extractProject,
            $this->loadProjectMetadata,
            $this->injectToViewMetadata,
            $this->render,
            $this->renderError,
            $this->objectAccessControl,
            $this->defaultErrorTemplate,
        );
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(
            JobGet::class,
            $this->jobGet,
        );
    }

    public function testPrepare(): void
    {
        self::assertInstanceOf(
            CookbookInterface::class,
            $this->jobGet->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
