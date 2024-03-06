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
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Cookbook\AccountInstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateNamespace;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateQuota;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateRegistryAccount;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateRole;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateRoleBinding;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateSecretServiceAccountToken;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateServiceAccount;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateStorage;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\PrepareAccountErrorHandler;
use Teknoo\Space\Recipe\Step\AccountCredential\PersistCredentials;

/**
 * Class AccountInstallTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license http://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Infrastructures\Kubernetes\Recipe\Cookbook\AccountInstall
 */
class AccountInstallTest extends TestCase
{
    private AccountInstall $accountInstall;

    private RecipeInterface|MockObject $recipe;

    private CreateNamespace|MockObject $createNamespace;

    private CreateServiceAccount|MockObject $createServiceAccount;

    private CreateQuota|MockObject $createQuota;

    private CreateRole|MockObject $createRole;

    private CreateRoleBinding|MockObject $createRoleBinding;

    private CreateSecretServiceAccountToken|MockObject $createSecret;

    private CreateStorage|MockObject $createStorage;

    private CreateRegistryAccount|MockObject $createRegistryAccount;

    private PersistCredentials|MockObject $persistCredentials;

    private PrepareAccountErrorHandler|MockObject $errorHandler;

    private string $defaultStorageSizeToClaim;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createMock(RecipeInterface::class);
        $this->createNamespace = $this->createMock(CreateNamespace::class);
        $this->createServiceAccount = $this->createMock(CreateServiceAccount::class);
        $this->createQuota = $this->createMock(CreateQuota::class);
        $this->createRole = $this->createMock(CreateRole::class);
        $this->createRoleBinding = $this->createMock(CreateRoleBinding::class);
        $this->createSecret = $this->createMock(CreateSecretServiceAccountToken::class);
        $this->createStorage = $this->createMock(CreateStorage::class);
        $this->createRegistryAccount = $this->createMock(CreateRegistryAccount::class);
        $this->persistCredentials = $this->createMock(PersistCredentials::class);
        $this->errorHandler = $this->createMock(PrepareAccountErrorHandler::class);
        $this->defaultStorageSizeToClaim = '42';
        $this->accountInstall = new AccountInstall(
            $this->recipe,
            $this->createNamespace,
            $this->createServiceAccount,
            $this->createQuota,
            $this->createRole,
            $this->createRoleBinding,
            $this->createSecret,
            $this->createStorage,
            $this->createRegistryAccount,
            $this->persistCredentials,
            $this->errorHandler,
            $this->defaultStorageSizeToClaim,
        );
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(
            AccountInstall::class,
            $this->accountInstall,
        );
    }

    public function testPrepare(): void
    {
        self::assertInstanceOf(
            CookbookInterface::class,
            $this->accountInstall->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
