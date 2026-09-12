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
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Infrastructures\AnsibleDockerCompose\Recipe\Plan\AccountEnvironmentInstall;
use Teknoo\Space\Infrastructures\AnsibleDockerCompose\Recipe\Plan\AccountEnvironmentReinstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReinstallAccountErrorHandler;
use Teknoo\Space\Recipe\Step\AccountEnvironment\FindEnvironmentInWallet;
use Teknoo\Space\Recipe\Step\AccountEnvironment\RemoveEnvironment;

/**
 * Class AccountEnvironmentReinstallTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountEnvironmentReinstall::class)]
class AccountEnvironmentReinstallTest extends TestCase
{
    private AccountEnvironmentReinstall $accountEnvironmentReinstall;

    private RecipeInterface&Stub $recipe;

    private FindEnvironmentInWallet&Stub $findEnvironmentInWallet;

    private RemoveEnvironment&Stub $removeEnvironment;

    private AccountEnvironmentInstall&Stub $accountEnvironmentInstall;

    private ReinstallAccountErrorHandler&Stub $errorHandler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createStub(RecipeInterface::class);
        $this->findEnvironmentInWallet = $this->createStub(FindEnvironmentInWallet::class);
        $this->removeEnvironment = $this->createStub(RemoveEnvironment::class);
        $this->accountEnvironmentInstall = $this->createStub(AccountEnvironmentInstall::class);
        $this->errorHandler = $this->createStub(ReinstallAccountErrorHandler::class);

        $this->accountEnvironmentReinstall = new AccountEnvironmentReinstall(
            recipe: $this->recipe,
            findEnvironmentInWallet: $this->findEnvironmentInWallet,
            removeEnvironment: $this->removeEnvironment,
            accountEnvironmentInstall: $this->accountEnvironmentInstall,
            errorHandler: $this->errorHandler,
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            AccountEnvironmentReinstall::class,
            $this->accountEnvironmentReinstall,
        );
    }

    public function testPrepare(): void
    {
        $this->assertInstanceOf(
            EditablePlanInterface::class,
            $this->accountEnvironmentReinstall->train(
                $this->createStub(ChefInterface::class),
            )
        );
    }
}
