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
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\Recipe;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Contracts\Recipe\Step\Job\FetchJobIdFromPendingInterface;
use Teknoo\Space\Recipe\Cookbook\JobPending;

/**
 * Class JobPendingTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license http://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Recipe\Cookbook\JobPending
 */
class JobPendingTest extends TestCase
{
    private JobPending $jobPending;

    private RecipeInterface|MockObject $recipe;

    private LoadObject|MockObject $loadObject;

    private FetchJobIdFromPendingInterface|MockObject $fetchJobIdFromPendingInterface;

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

        $this->recipe = $this->createMock(Recipe::class);
        $this->loadObject = $this->createMock(LoadObject::class);
        $this->fetchJobIdFromPendingInterface = $this->createMock(FetchJobIdFromPendingInterface::class);
        $this->render = $this->createMock(Render::class);
        $this->renderError = $this->createMock(RenderError::class);
        $this->objectAccessControl = $this->createMock(ObjectAccessControlInterface::class);
        $this->defaultErrorTemplate = '42';
        $this->jobPending = new JobPending(
            $this->recipe,
            $this->loadObject,
            $this->objectAccessControl,
            $this->fetchJobIdFromPendingInterface,
            $this->render,
            $this->renderError,
            $this->defaultErrorTemplate,
        );
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(
            JobPending::class,
            $this->jobPending,
        );
    }

    public function testPrepare(): void
    {
        self::assertInstanceOf(
            CookbookInterface::class,
            $this->jobPending->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
