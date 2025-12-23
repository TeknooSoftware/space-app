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
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Plan\AccountRegistryInstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Plan\AccountRegistryReinstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReinstallAccountErrorHandler;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReloadNamespace;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Client\SetRedirectClientAtEnd;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;
use Teknoo\Space\Recipe\Step\Account\PrepareRedirection;
use Teknoo\Space\Recipe\Step\Account\UpdateAccountHistory;
use Teknoo\Space\Recipe\Step\AccountRegistry\LoadRegistryCredential;
use Teknoo\Space\Recipe\Step\AccountRegistry\RemoveRegistryCredential;

/**
 * Class AccountRegistryReinstallTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountRegistryReinstall::class)]
class AccountRegistryReinstallTest extends TestCase
{
    private AccountRegistryReinstall $accountRegistryReinstall;

    private RecipeInterface&Stub $recipe;

    private LoadObject&Stub $loadObject;

    private PrepareRedirection&Stub $prepareRedirection;

    private SetRedirectClientAtEnd&Stub $redirectClient;

    private LoadHistory&Stub $loadHistory;

    private LoadRegistryCredential&Stub $loadRegistryCredential;

    private RemoveRegistryCredential&Stub $removeRegistryCredentials;

    private AccountRegistryInstall&Stub $accountRegistryInstall;

    private ReloadNamespace&Stub $reloadNamespace;

    private UpdateAccountHistory&Stub $updateAccountHistory;

    private JumpIf&Stub $jumpIf;

    private Render&Stub $render;

    private ReinstallAccountErrorHandler&Stub $errorHandler;

    private ObjectAccessControlInterface&Stub $objectAccessControl;

    private string $defaultStorageSizeToClaim;

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
        $this->loadRegistryCredential = $this->createStub(LoadRegistryCredential::class);
        $this->removeRegistryCredentials = $this->createStub(RemoveRegistryCredential::class);
        $this->reloadNamespace = $this->createStub(ReloadNamespace::class);
        $this->accountRegistryInstall = $this->createStub(AccountRegistryInstall::class);
        $this->updateAccountHistory = $this->createStub(UpdateAccountHistory::class);
        $this->jumpIf = $this->createStub(JumpIf::class);
        $this->render = $this->createStub(Render::class);
        $this->errorHandler = $this->createStub(ReinstallAccountErrorHandler::class);
        $this->objectAccessControl = $this->createStub(ObjectAccessControlInterface::class);
        $this->defaultStorageSizeToClaim = '42';

        $this->accountRegistryReinstall = new AccountRegistryReinstall(
            recipe: $this->recipe,
            loadObject: $this->loadObject,
            prepareRedirection: $this->prepareRedirection,
            redirectClient: $this->redirectClient,
            loadHistory: $this->loadHistory,
            loadRegistryCredential: $this->loadRegistryCredential,
            reloadNamespace: $this->reloadNamespace,
            removeRegistryCredential: $this->removeRegistryCredentials,
            accountRegistryInstall: $this->accountRegistryInstall,
            updateAccountHistory: $this->updateAccountHistory,
            jumpIf: $this->jumpIf,
            render: $this->render,
            errorHandler: $this->errorHandler,
            objectAccessControl: $this->objectAccessControl,
            defaultStorageSizeToClaim: $this->defaultStorageSizeToClaim,
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            AccountRegistryReinstall::class,
            $this->accountRegistryReinstall,
        );
    }

    public function testPrepare(): void
    {
        $this->assertInstanceOf(
            EditablePlanInterface::class,
            $this->accountRegistryReinstall->train(
                $this->createStub(ChefInterface::class),
            )
        );
    }
}
