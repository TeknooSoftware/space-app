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

namespace Teknoo\Space\Tests\Unit\Infrastructures\AnsibleDockerCompose\Recipe\Plan;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Recipe\Step\JumpIf;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Infrastructures\AnsibleDockerCompose\Recipe\Plan\AccountEnvironmentInstall;
use Teknoo\Space\Infrastructures\AnsibleDockerCompose\Recipe\Plan\AccountEnvironmentReinstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReinstallAccountErrorHandler;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Client\SetRedirectClientAtEnd;
use Teknoo\Space\Recipe\Step\Account\PrepareRedirection;
use Teknoo\Space\Recipe\Step\Account\UpdateAccountHistory;
use Teknoo\Space\Recipe\Step\AccountEnvironment\FindEnvironmentInWallet;
use Teknoo\Space\Recipe\Step\AccountEnvironment\LoadEnvironments;
use Teknoo\Space\Recipe\Step\AccountEnvironment\RemoveEnvironment;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;

/**
 * Class AccountEnvironmentReinstallTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountEnvironmentReinstall::class)]
class AccountEnvironmentReinstallTest extends TestCase
{
    private AccountEnvironmentReinstall $plan;

    private RecipeInterface&Stub $recipe;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createStub(RecipeInterface::class);

        $this->plan = new AccountEnvironmentReinstall(
            recipe: $this->recipe,
            loadObject: $this->createStub(LoadObject::class),
            prepareRedirection: $this->createStub(PrepareRedirection::class),
            redirectClient: $this->createStub(SetRedirectClientAtEnd::class),
            loadHistory: $this->createStub(LoadHistory::class),
            loadEnvironments: $this->createStub(LoadEnvironments::class),
            findEnvironmentInWallet: $this->createStub(FindEnvironmentInWallet::class),
            removeEnvironment: $this->createStub(RemoveEnvironment::class),
            accountEnvironmentInstall: $this->createStub(AccountEnvironmentInstall::class),
            updateAccountHistory: $this->createStub(UpdateAccountHistory::class),
            jumpIf: $this->createStub(JumpIf::class),
            render: $this->createStub(Render::class),
            errorHandler: $this->createStub(ReinstallAccountErrorHandler::class),
            objectAccessControl: $this->createStub(ObjectAccessControlInterface::class),
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(AccountEnvironmentReinstall::class, $this->plan);
    }

    public function testPrepare(): void
    {
        $this->assertInstanceOf(
            EditablePlanInterface::class,
            $this->plan->train($this->createStub(ChefInterface::class)),
        );
    }
}
