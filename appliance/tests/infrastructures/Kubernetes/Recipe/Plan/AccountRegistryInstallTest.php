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
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Plan\AccountRegistryInstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateNamespace;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\PrepareAccountErrorHandler;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Registry\CreateRegistryDeployment;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Registry\CreateStorage;
use Teknoo\Space\Recipe\Step\AccountCluster\LoadAccountClusters;
use Teknoo\Space\Recipe\Step\AccountRegistry\PersistRegistryCredential;

/**
 * Class AccountRegistryInstallTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountRegistryInstall::class)]
class AccountRegistryInstallTest extends TestCase
{
    private AccountRegistryInstall $accountInstall;

    private RecipeInterface&Stub $recipe;

    private LoadAccountClusters&Stub $loadAccountClusters;

    private CreateNamespace&Stub $createNamespace;

    private CreateStorage&Stub $createStorage;

    private CreateRegistryDeployment&Stub $createRegistryAccount;

    private PersistRegistryCredential&Stub $persistRegistryCredentials;

    private PrepareAccountErrorHandler&Stub $errorHandler;

    private ObjectAccessControlInterface&Stub $objectAccessControlInterface;

    private string $defaultStorageSizeToClaim;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createStub(RecipeInterface::class);
        $this->loadAccountClusters = $this->createStub(LoadAccountClusters::class);
        $this->createNamespace = $this->createStub(CreateNamespace::class);
        $this->createStorage = $this->createStub(CreateStorage::class);
        $this->createRegistryAccount = $this->createStub(CreateRegistryDeployment::class);
        $this->persistRegistryCredentials = $this->createStub(PersistRegistryCredential::class);
        $this->errorHandler = $this->createStub(PrepareAccountErrorHandler::class);
        $this->objectAccessControlInterface = $this->createStub(ObjectAccessControlInterface::class);
        $this->defaultStorageSizeToClaim = '42';

        $this->accountInstall = new AccountRegistryInstall(
            recipe: $this->recipe,
            loadAccountClusters: $this->loadAccountClusters,
            createNamespace: $this->createNamespace,
            createStorage: $this->createStorage,
            createRegistryAccount: $this->createRegistryAccount,
            persistRegistryCredential: $this->persistRegistryCredentials,
            errorHandler: $this->errorHandler,
            objectAccessControl: $this->objectAccessControlInterface,
            defaultStorageSizeToClaim: $this->defaultStorageSizeToClaim,
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            AccountRegistryInstall::class,
            $this->accountInstall,
        );
    }

    public function testPrepare(): void
    {
        $this->assertInstanceOf(
            EditablePlanInterface::class,
            $this->accountInstall->train(
                $this->createStub(ChefInterface::class),
            )
        );
    }
}
