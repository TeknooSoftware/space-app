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
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\ClustersInfoInterface;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\HealthInterface;
use Teknoo\Space\Recipe\Plan\Dashboard;
use Teknoo\Space\Recipe\Step\AccountEnvironment\LoadEnvironments;
use Teknoo\Space\Recipe\Step\Misc\ClusterAndEnvSelection;

/**
 * Class DashboardTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license https://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(Dashboard::class)]
class DashboardTest extends TestCase
{
    private Dashboard $dashboard;

    private RecipeInterface|MockObject $recipe;

    private HealthInterface|MockObject $health;

    private LoadEnvironments|MockObject $loadEnvironments;

    private ClustersInfoInterface|MockObject $clustersInfo;

    private ClusterAndEnvSelection|MockObject $clusterAndEnvSelection;

    private Render|MockObject $render;

    private RenderError|MockObject $renderError;

    private string|Stringable $defaultErrorTemplate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createMock(RecipeInterface::class);
        $this->health = $this->createMock(HealthInterface::class);
        $this->loadEnvironments = $this->createMock(LoadEnvironments::class);
        $this->clustersInfo = $this->createMock(ClustersInfoInterface::class);
        $this->clusterAndEnvSelection = $this->createMock(ClusterAndEnvSelection::class);
        $this->render = $this->createMock(Render::class);
        $this->renderError = $this->createMock(RenderError::class);
        $this->defaultErrorTemplate = '42';
        $this->dashboard = new Dashboard(
            recipe: $this->recipe,
            health: $this->health,
            loadEnvironments: $this->loadEnvironments,
            clustersInfo: $this->clustersInfo,
            clusterAndEnvSelection: $this->clusterAndEnvSelection,
            render: $this->render,
            renderError: $this->renderError,
            defaultErrorTemplate: $this->defaultErrorTemplate,
        );
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(
            Dashboard::class,
            $this->dashboard,
        );
    }

    public function testPrepare(): void
    {
        self::assertInstanceOf(
            EditablePlanInterface::class,
            $this->dashboard->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
