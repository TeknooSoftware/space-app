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
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Infrastructures\Kubernetes\Recipe\Cookbook;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Recipe\Step\JumpIf;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Cookbook\AccountRegistryInstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Cookbook\AccountRegistryReinstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReinstallAccountErrorHandler;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReloadNamespace;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Client\SetRedirectClientAtEnd;
use Teknoo\Space\Recipe\Cookbook\Traits\PrepareAccountTrait;
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
 * @license http://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversTrait(PrepareAccountTrait::class)]
#[CoversClass(AccountRegistryReinstall::class)]
class AccountRegistryReinstallTest extends TestCase
{
    private AccountRegistryReinstall $accountRegistryReinstall;

    private RecipeInterface|MockObject $recipe;

    private LoadObject|MockObject $loadObject;

    private PrepareRedirection|MockObject $prepareRedirection;

    private SetRedirectClientAtEnd|MockObject $redirectClient;

    private LoadHistory|MockObject $loadHistory;

    private LoadRegistryCredential|MockObject $loadRegistryCredential;

    private RemoveRegistryCredential|MockObject $removeRegistryCredentials;

    private AccountRegistryInstall|MockObject $accountRegistryInstall;

    private ReloadNamespace|MockObject $reloadNamespace;

    private UpdateAccountHistory|MockObject $updateAccountHistory;

    private JumpIf|MockObject $jumpIf;

    private Render|MockObject $render;

    private ReinstallAccountErrorHandler|MockObject $errorHandler;

    private ObjectAccessControlInterface|MockObject $objectAccessControl;

    private string $defaultStorageSizeToClaim;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createMock(RecipeInterface::class);
        $this->loadObject = $this->createMock(LoadObject::class);
        $this->prepareRedirection = $this->createMock(PrepareRedirection::class);
        $this->redirectClient = $this->createMock(SetRedirectClientAtEnd::class);
        $this->loadHistory = $this->createMock(LoadHistory::class);
        $this->loadRegistryCredential = $this->createMock(LoadRegistryCredential::class);
        $this->removeRegistryCredentials = $this->createMock(RemoveRegistryCredential::class);
        $this->reloadNamespace = $this->createMock(ReloadNamespace::class);
        $this->accountRegistryInstall = $this->createMock(AccountRegistryInstall::class);
        $this->updateAccountHistory = $this->createMock(UpdateAccountHistory::class);
        $this->jumpIf = $this->createMock(JumpIf::class);
        $this->render = $this->createMock(Render::class);
        $this->errorHandler = $this->createMock(ReinstallAccountErrorHandler::class);
        $this->objectAccessControl = $this->createMock(ObjectAccessControlInterface::class);
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
        self::assertInstanceOf(
            AccountRegistryReinstall::class,
            $this->accountRegistryReinstall,
        );
    }

    public function testPrepare(): void
    {
        self::assertInstanceOf(
            CookbookInterface::class,
            $this->accountRegistryReinstall->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
