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
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\DashboardFrameInterface;
use Teknoo\Space\Recipe\Plan\DashboardFrame;
use Teknoo\Space\Recipe\Step\AccountEnvironment\LoadEnvironments;

/**
 * Class DashboardFrameTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license https://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(DashboardFrame::class)]
class DashboardFrameTest extends TestCase
{
    private DashboardFrame $dashboardFrame;

    private RecipeInterface|MockObject $recipe;

    private LoadEnvironments|MockObject $loadEnvironments;

    private DashboardFrameInterface|MockObject $dashboard;

    private RenderError|MockObject $renderError;

    private string|Stringable $defaultErrorTemplate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createMock(RecipeInterface::class);
        $this->loadEnvironments = $this->createMock(LoadEnvironments::class);
        $this->dashboard = $this->createMock(DashboardFrameInterface::class);
        $this->renderError = $this->createMock(RenderError::class);
        $this->defaultErrorTemplate = '42';
        $this->dashboardFrame = new DashboardFrame(
            recipe: $this->recipe,
            loadEnvironments: $this->loadEnvironments,
            dashboard: $this->dashboard,
            renderError: $this->renderError,
            defaultErrorTemplate: $this->defaultErrorTemplate,
        );
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(
            DashboardFrame::class,
            $this->dashboardFrame,
        );
    }

    public function testPrepare(): void
    {
        self::assertInstanceOf(
            EditablePlanInterface::class,
            $this->dashboardFrame->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
