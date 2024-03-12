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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Cookbook\AccountInstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Cookbook\AccountReinstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReinstallAccountErrorHandler;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Client\SetRedirectClientAtEnd;
use Teknoo\Space\Recipe\Step\AccountCredential\LoadCredentials;
use Teknoo\Space\Recipe\Step\AccountCredential\RemoveCredentials;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;
use Teknoo\Space\Recipe\Step\Account\PrepareRedirection;
use Teknoo\Space\Recipe\Step\Account\SetAccountNamespace;
use Teknoo\Space\Recipe\Step\Account\UpdateAccountHistory;
use Teknoo\Space\Recipe\Step\AccountRegistry\LoadRegistryCredentials;
use Teknoo\Space\Recipe\Step\AccountRegistry\RemoveRegistryCredentials;

/**
 * Class AccountReinstallTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license http://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Infrastructures\Kubernetes\Recipe\Cookbook\AccountReinstall
 * @covers \Teknoo\Space\Recipe\Cookbook\Traits\PrepareAccountTrait
 */
class AccountReinstallTest extends TestCase
{
    private AccountReinstall $accountReinstall;

    private RecipeInterface|MockObject $recipe;

    private LoadObject|MockObject $loadObject;

    private PrepareRedirection|MockObject $prepareRedirection;

    private SetRedirectClientAtEnd|MockObject $redirectClient;

    private LoadHistory|MockObject $loadHistory;

    private LoadCredentials|MockObject $loadCredentials;

    private LoadRegistryCredentials|MockObject $loadRegistryCredentials;

    private RemoveCredentials|MockObject $removeCredentials;

    private RemoveRegistryCredentials|MockObject $removeRegistryCredentials;

    private SetAccountNamespace|MockObject $setAccountNamespace;

    private AccountInstall|MockObject $installAccount;

    private UpdateAccountHistory|MockObject $updateAccountHistory;

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
        $this->loadCredentials = $this->createMock(LoadCredentials::class);
        $this->loadRegistryCredentials = $this->createMock(LoadRegistryCredentials::class);
        $this->removeCredentials = $this->createMock(RemoveCredentials::class);
        $this->removeRegistryCredentials = $this->createMock(RemoveRegistryCredentials::class);
        $this->setAccountNamespace = $this->createMock(SetAccountNamespace::class);
        $this->installAccount = $this->createMock(AccountInstall::class);
        $this->updateAccountHistory = $this->createMock(UpdateAccountHistory::class);
        $this->errorHandler = $this->createMock(ReinstallAccountErrorHandler::class);
        $this->objectAccessControl = $this->createMock(ObjectAccessControlInterface::class);
        $this->defaultStorageSizeToClaim = '42';
        $this->accountReinstall = new AccountReinstall(
            $this->recipe,
            $this->loadObject,
            $this->prepareRedirection,
            $this->redirectClient,
            $this->loadHistory,
            $this->loadCredentials,
            $this->loadRegistryCredentials,
            $this->removeCredentials,
            $this->removeRegistryCredentials,
            $this->setAccountNamespace,
            $this->installAccount,
            $this->updateAccountHistory,
            $this->errorHandler,
            $this->objectAccessControl,
            $this->defaultStorageSizeToClaim,
        );
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(
            AccountReinstall::class,
            $this->accountReinstall,
        );
    }

    public function testPrepare(): void
    {
        self::assertInstanceOf(
            CookbookInterface::class,
            $this->accountReinstall->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
