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
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Plan\AccountEnvironmentInstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateNamespace;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\PrepareAccountErrorHandler;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateDockerSecret;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateQuota;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateRole;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateRoleBinding;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateSecretServiceAccountToken;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateServiceAccount;
use Teknoo\Space\Recipe\Step\AccountCluster\LoadAccountClusters;
use Teknoo\Space\Recipe\Step\AccountEnvironment\PersistEnvironment;
use Teknoo\Space\Recipe\Step\ClusterConfig\SelectClusterConfig;

/**
 * Class AccountEnvironmentInstallTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountEnvironmentInstall::class)]
class AccountEnvironmentInstallTest extends TestCase
{
    private AccountEnvironmentInstall $accountInstall;

    private RecipeInterface&Stub $recipe;

    private LoadAccountClusters&Stub $loadAccountClusters;

    private CreateNamespace&Stub $createNamespace;

    private SelectClusterConfig&Stub $selectClusterConfig;

    private CreateServiceAccount&Stub $createServiceAccount;

    private CreateQuota&Stub $createQuota;

    private CreateRole&Stub $createRole;

    private CreateRoleBinding&Stub $createRoleBinding;

    private CreateDockerSecret&Stub $createDockerSecret;

    private CreateSecretServiceAccountToken&Stub $createSecret;

    private PersistEnvironment&Stub $persistCredentials;

    private PrepareAccountErrorHandler&Stub $errorHandler;

    private ObjectAccessControlInterface&Stub $objectAccessControlInterface;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createStub(RecipeInterface::class);
        $this->loadAccountClusters = $this->createStub(LoadAccountClusters::class);
        $this->createNamespace = $this->createStub(CreateNamespace::class);
        $this->selectClusterConfig = $this->createStub(SelectClusterConfig::class);
        $this->createServiceAccount = $this->createStub(CreateServiceAccount::class);
        $this->createQuota = $this->createStub(CreateQuota::class);
        $this->createRole = $this->createStub(CreateRole::class);
        $this->createRoleBinding = $this->createStub(CreateRoleBinding::class);
        $this->createDockerSecret = $this->createStub(CreateDockerSecret::class);
        $this->createSecret = $this->createStub(CreateSecretServiceAccountToken::class);
        $this->persistCredentials = $this->createStub(PersistEnvironment::class);
        $this->errorHandler = $this->createStub(PrepareAccountErrorHandler::class);
        $this->objectAccessControlInterface = $this->createStub(ObjectAccessControlInterface::class);

        $this->accountInstall = new AccountEnvironmentInstall(
            recipe: $this->recipe,
            loadAccountClusters: $this->loadAccountClusters,
            createNamespace: $this->createNamespace,
            selectClusterConfig: $this->selectClusterConfig,
            createServiceAccount: $this->createServiceAccount,
            createQuota: $this->createQuota,
            createRole: $this->createRole,
            createRoleBinding: $this->createRoleBinding,
            createDockerSecret: $this->createDockerSecret,
            createSecret: $this->createSecret,
            persistCredentials: $this->persistCredentials,
            errorHandler: $this->errorHandler,
            objectAccessControl: $this->objectAccessControlInterface,
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            AccountEnvironmentInstall::class,
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
