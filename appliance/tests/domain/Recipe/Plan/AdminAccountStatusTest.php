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
use PHPUnit\Framework\TestCase;
use Stringable;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Recipe\Plan\AdminAccountStatus;
use Teknoo\Space\Recipe\Step\Account\LoadSubscriptionPlan;
use Teknoo\Space\Recipe\Step\AccountEnvironment\CreateResumes;
use Teknoo\Space\Recipe\Step\AccountEnvironment\LoadEnvironments;
use Teknoo\Space\Recipe\Step\Subscription\InjectStatus;

/**
 * Class AdminAccountStatusTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AdminAccountStatus::class)]
class AdminAccountStatusTest extends TestCase
{
    private AdminAccountStatus $adminAccountStatus;

    private RecipeInterface&MockObject $recipe;

    private LoadObject&MockObject $loadObject;

    private LoadSubscriptionPlan&MockObject $loadSubscriptionPlan;

    private LoadEnvironments&MockObject $loadEnvironments;

    private CreateResumes&MockObject $createResumes;

    private InjectStatus&MockObject $injectStatus;

    private Render&MockObject $render;

    private RenderError&MockObject $renderError;

    private string|Stringable $defaultErrorTemplate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createMock(RecipeInterface::class);
        $this->loadObject = $this->createMock(LoadObject::class);
        $this->loadSubscriptionPlan = $this->createMock(LoadSubscriptionPlan::class);
        $this->loadEnvironments = $this->createMock(LoadEnvironments::class);
        $this->createResumes = $this->createMock(CreateResumes::class);
        $this->injectStatus = $this->createMock(InjectStatus::class);
        $this->render = $this->createMock(Render::class);
        $this->renderError = $this->createMock(RenderError::class);
        $this->defaultErrorTemplate = '42';

        $this->adminAccountStatus = new AdminAccountStatus(
            recipe: $this->recipe,
            loadObject: $this->loadObject,
            loadSubscriptionPlan: $this->loadSubscriptionPlan,
            loadEnvironments: $this->loadEnvironments,
            createResumes: $this->createResumes,
            injectStatus: $this->injectStatus,
            render: $this->render,
            renderError: $this->renderError,
            defaultErrorTemplate: $this->defaultErrorTemplate,
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            AdminAccountStatus::class,
            $this->adminAccountStatus,
        );
    }

    public function testPrepare(): void
    {
        $this->assertInstanceOf(
            EditablePlanInterface::class,
            $this->adminAccountStatus->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
