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
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - AccountClusterDelete@teknoo.software)
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
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Recipe\Step\DeleteObject;
use Teknoo\East\Common\Recipe\Step\JumpIf;
use Teknoo\East\Common\Recipe\Step\JumpIfNot;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Recipe\Plan\AccountClusterDelete;
use Teknoo\Space\Recipe\Step\Account\InjectToView;

/**
 * Class AccountClusterDeleteTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - AccountClusterDelete@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountClusterDelete::class)]
class AccountClusterDeleteTest extends TestCase
{
    private AccountClusterDelete $AccountClusterDelete;

    private RecipeInterface&Stub $recipe;

    private JumpIfNot&Stub $jumpIfNot;

    private LoadObject&Stub $loadObject;

    private InjectToView&Stub $injectToView;

    private ObjectAccessControlInterface&Stub $objectAccessControl;

    private DeleteObject&Stub $deleteObject;

    private JumpIf&Stub $jumpIf;

    private RedirectClientInterface&Stub $redirectClient;

    private Render&Stub $render;

    private RenderError&Stub $renderError;

    private string|Stringable $defaultErrorTemplate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createStub(RecipeInterface::class);
        $this->jumpIfNot = $this->createStub(JumpIfNot::class);
        $this->loadObject = $this->createStub(LoadObject::class);
        $this->objectAccessControl = $this->createStub(ObjectAccessControlInterface::class);
        $this->deleteObject = $this->createStub(DeleteObject::class);
        $this->jumpIf = $this->createStub(JumpIf::class);
        $this->redirectClient = $this->createStub(RedirectClientInterface::class);
        $this->injectToView = $this->createStub(InjectToView::class);
        $this->render = $this->createStub(Render::class);
        $this->renderError = $this->createStub(RenderError::class);

        $this->defaultErrorTemplate = '42';

        $this->AccountClusterDelete = new AccountClusterDelete(
            recipe: $this->recipe,
            jumpIfNot: $this->jumpIfNot,
            loadObject: $this->loadObject,
            injectToView: $this->injectToView,
            deleteObject: $this->deleteObject,
            jumpIf: $this->jumpIf,
            redirectClient: $this->redirectClient,
            render: $this->render,
            renderError: $this->renderError,
            objectAccessControl: $this->objectAccessControl,
            defaultErrorTemplate: $this->defaultErrorTemplate,
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            AccountClusterDelete::class,
            $this->AccountClusterDelete,
        );
    }

    public function testPrepare(): void
    {
        $this->assertInstanceOf(
            EditablePlanInterface::class,
            $this->AccountClusterDelete->train(
                $this->createStub(ChefInterface::class),
            )
        );
    }
}
