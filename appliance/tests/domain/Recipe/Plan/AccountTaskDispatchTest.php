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
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Recipe\Step\JumpIf;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Contracts\Recipe\Step\Task\CallNewTaskInterface;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Client\SetRedirectClientAtEnd;
use Teknoo\Space\Recipe\Plan\AccountTaskDispatch;
use Teknoo\Space\Recipe\Step\Account\PrepareRedirection;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;
use Teknoo\Space\Recipe\Step\Task\AddTaskToHistory;
use Teknoo\Space\Recipe\Step\Task\PrepareAccountTask;

/**
 * Class AccountTaskDispatchTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(AccountTaskDispatch::class)]
class AccountTaskDispatchTest extends TestCase
{
    private AccountTaskDispatch $accountTaskDispatch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->accountTaskDispatch = new AccountTaskDispatch(
            recipe: $this->createStub(RecipeInterface::class),
            loadObject: $this->createStub(LoadObject::class),
            objectAccessControl: $this->createStub(ObjectAccessControlInterface::class),
            loadHistory: $this->createStub(LoadHistory::class),
            prepareAccountTask: $this->createStub(PrepareAccountTask::class),
            callNewTask: $this->createStub(CallNewTaskInterface::class),
            addTaskToHistory: $this->createStub(AddTaskToHistory::class),
            prepareRedirection: $this->createStub(PrepareRedirection::class),
            jumpIf: $this->createStub(JumpIf::class),
            redirectClient: $this->createStub(SetRedirectClientAtEnd::class),
            render: $this->createStub(Render::class),
            renderError: $this->createStub(RenderError::class),
            defaultErrorTemplate: 'foo',
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            AccountTaskDispatch::class,
            $this->accountTaskDispatch,
        );
    }

    public function testPrepare(): void
    {
        $this->assertInstanceOf(
            EditablePlanInterface::class,
            $this->accountTaskDispatch->train(
                $this->createStub(ChefInterface::class),
            )
        );
    }
}
