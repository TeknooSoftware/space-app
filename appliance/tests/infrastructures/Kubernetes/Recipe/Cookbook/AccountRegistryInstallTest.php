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
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Cookbook\AccountRegistryInstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateNamespace;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\PrepareAccountErrorHandler;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Registry\CreateRegistryDeployment;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Registry\CreateStorage;
use Teknoo\Space\Recipe\Step\AccountRegistry\PersistRegistryCredential;

/**
 * Class AccountRegistryInstallTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license http://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Infrastructures\Kubernetes\Recipe\Cookbook\AccountRegistryInstall
 */
class AccountRegistryInstallTest extends TestCase
{
    private AccountRegistryInstall $accountInstall;

    private RecipeInterface|MockObject $recipe;

    private CreateNamespace|MockObject $createNamespace;

    private CreateStorage|MockObject $createStorage;

    private CreateRegistryDeployment|MockObject $createRegistryAccount;

    private PersistRegistryCredential|MockObject $persistRegistryCredentials;

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
        $this->createStorage = $this->createMock(CreateStorage::class);
        $this->createRegistryAccount = $this->createMock(CreateRegistryDeployment::class);
        $this->persistRegistryCredentials = $this->createMock(PersistRegistryCredential::class);
        $this->errorHandler = $this->createMock(PrepareAccountErrorHandler::class);
        $this->defaultStorageSizeToClaim = '42';

        $this->accountInstall = new AccountRegistryInstall(
            recipe: $this->recipe,
            createNamespace: $this->createNamespace,
            createStorage: $this->createStorage,
            createRegistryAccount: $this->createRegistryAccount,
            persistRegistryCredential: $this->persistRegistryCredentials,
            errorHandler: $this->errorHandler,
            defaultStorageSizeToClaim: $this->defaultStorageSizeToClaim,
        );
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(
            AccountRegistryInstall::class,
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
