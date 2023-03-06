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
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\DashboardFrameInterface;
use Teknoo\Space\Recipe\Cookbook\DashboardFrame;
use Teknoo\Space\Recipe\Step\AccountCredential\LoadCredentials;

/**
 * Class DashboardFrameTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license http://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Recipe\Cookbook\DashboardFrame
 */
class DashboardFrameTest extends TestCase
{
    private DashboardFrame $dashboardFrame;

    private RecipeInterface|MockObject $recipe;

    private LoadCredentials|MockObject $loadCredentials;

    private DashboardFrameInterface|MockObject $dashboard;

    private RenderError|MockObject $renderError;

    private string $defaultErrorTemplate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createMock(RecipeInterface::class);
        $this->loadCredentials = $this->createMock(LoadCredentials::class);
        $this->dashboard = $this->createMock(DashboardFrameInterface::class);
        $this->renderError = $this->createMock(RenderError::class);
        $this->defaultErrorTemplate = '42';
        $this->dashboardFrame = new DashboardFrame(
            $this->recipe,
            $this->loadCredentials,
            $this->dashboard,
            $this->renderError,
            $this->defaultErrorTemplate,
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
            CookbookInterface::class,
            $this->dashboardFrame->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
