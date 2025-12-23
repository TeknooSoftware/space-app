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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Kubernetes\Recipe\Plan;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Recipe\Step\JumpIf;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Plan\AccountEnvironmentInstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Plan\AccountEnvironmentReinstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReinstallAccountErrorHandler;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReloadNamespace;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Client\SetRedirectClientAtEnd;
use Teknoo\Space\Recipe\Step\AccountEnvironment\FindEnvironmentInWallet;
use Teknoo\Space\Recipe\Step\AccountEnvironment\LoadEnvironments;
use Teknoo\Space\Recipe\Step\AccountEnvironment\RemoveEnvironment;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;
use Teknoo\Space\Recipe\Step\Account\PrepareRedirection;
use Teknoo\Space\Recipe\Step\Account\UpdateAccountHistory;
use Teknoo\Space\Recipe\Step\AccountRegistry\LoadRegistryCredential;

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
    private AccountEnvironmentReinstall $accountReinstall;

    private RecipeInterface&Stub $recipe;

    private LoadObject&Stub $loadObject;

    private PrepareRedirection&Stub $prepareRedirection;

    private SetRedirectClientAtEnd&Stub $redirectClient;

    private LoadHistory&Stub $loadHistory;

    private LoadEnvironments&Stub $loadEnvironments;

    private LoadRegistryCredential&Stub $loadRegistryCredential;

    private ReloadNamespace&Stub $reloadNamespace;

    private FindEnvironmentInWallet&Stub $findEnvironmentInWallet;

    private RemoveEnvironment&Stub $removeCredentials;

    private AccountEnvironmentInstall&Stub $accountEnvironmentInstall;

    private UpdateAccountHistory&Stub $updateAccountHistory;

    private JumpIf&Stub $jumpIf;

    private Render&Stub $render;

    private ReinstallAccountErrorHandler&Stub $errorHandler;

    private ObjectAccessControlInterface&Stub $objectAccessControl;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createStub(RecipeInterface::class);
        $this->loadObject = $this->createStub(LoadObject::class);
        $this->prepareRedirection = $this->createStub(PrepareRedirection::class);
        $this->redirectClient = $this->createStub(SetRedirectClientAtEnd::class);
        $this->loadHistory = $this->createStub(LoadHistory::class);
        $this->loadEnvironments = $this->createStub(LoadEnvironments::class);
        $this->loadRegistryCredential = $this->createStub(LoadRegistryCredential::class);
        $this->reloadNamespace = $this->createStub(ReloadNamespace::class);
        $this->findEnvironmentInWallet = $this->createStub(FindEnvironmentInWallet::class);
        $this->removeCredentials = $this->createStub(RemoveEnvironment::class);
        $this->accountEnvironmentInstall = $this->createStub(AccountEnvironmentInstall::class);
        $this->updateAccountHistory = $this->createStub(UpdateAccountHistory::class);
        $this->jumpIf = $this->createStub(JumpIf::class);
        $this->render = $this->createStub(Render::class);
        $this->errorHandler = $this->createStub(ReinstallAccountErrorHandler::class);
        $this->objectAccessControl = $this->createStub(ObjectAccessControlInterface::class);

        $this->accountReinstall = new AccountEnvironmentReinstall(
            recipe: $this->recipe,
            loadObject: $this->loadObject,
            prepareRedirection: $this->prepareRedirection,
            redirectClient: $this->redirectClient,
            loadHistory: $this->loadHistory,
            loadEnvironments: $this->loadEnvironments,
            loadRegistryCredential: $this->loadRegistryCredential,
            reloadNamespace: $this->reloadNamespace,
            findEnvironmentInWallet: $this->findEnvironmentInWallet,
            removeEnvironment: $this->removeCredentials,
            accountEnvironmentInstall: $this->accountEnvironmentInstall,
            updateAccountHistory: $this->updateAccountHistory,
            jumpIf: $this->jumpIf,
            render: $this->render,
            errorHandler: $this->errorHandler,
            objectAccessControl: $this->objectAccessControl,
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            AccountEnvironmentReinstall::class,
            $this->accountReinstall,
        );
    }

    public function testPrepare(): void
    {
        $this->assertInstanceOf(
            EditablePlanInterface::class,
            $this->accountReinstall->train(
                $this->createStub(ChefInterface::class),
            )
        );
    }
}
