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
 * @link        https://teknoo.software/applications/space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Infrastructures\Kubernetes\Recipe\Plan;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
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
use Teknoo\Space\Recipe\Step\AccountEnvironment\PersistEnvironment;
use Teknoo\Space\Recipe\Step\ClusterConfig\SelectClusterConfig;

/**
 * Class AccountEnvironmentInstallTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license http://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountEnvironmentInstall::class)]
class AccountEnvironmentInstallTest extends TestCase
{
    private AccountEnvironmentInstall $accountInstall;

    private RecipeInterface|MockObject $recipe;

    private CreateNamespace|MockObject $createNamespace;

    private SelectClusterConfig|MockObject $selectClusterConfig;

    private CreateServiceAccount|MockObject $createServiceAccount;

    private CreateQuota|MockObject $createQuota;

    private CreateRole|MockObject $createRole;

    private CreateRoleBinding|MockObject $createRoleBinding;

    private CreateDockerSecret|MockObject $createDockerSecret;

    private CreateSecretServiceAccountToken|MockObject $createSecret;

    private PersistEnvironment|MockObject $persistCredentials;

    private PrepareAccountErrorHandler|MockObject $errorHandler;

    private ObjectAccessControlInterface|MockObject $objectAccessControlInterface;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createMock(RecipeInterface::class);
        $this->createNamespace = $this->createMock(CreateNamespace::class);
        $this->selectClusterConfig = $this->createMock(SelectClusterConfig::class);
        $this->createServiceAccount = $this->createMock(CreateServiceAccount::class);
        $this->createQuota = $this->createMock(CreateQuota::class);
        $this->createRole = $this->createMock(CreateRole::class);
        $this->createRoleBinding = $this->createMock(CreateRoleBinding::class);
        $this->createDockerSecret = $this->createMock(CreateDockerSecret::class);
        $this->createSecret = $this->createMock(CreateSecretServiceAccountToken::class);
        $this->persistCredentials = $this->createMock(PersistEnvironment::class);
        $this->errorHandler = $this->createMock(PrepareAccountErrorHandler::class);
        $this->objectAccessControlInterface = $this->createMock(ObjectAccessControlInterface::class);

        $this->accountInstall = new AccountEnvironmentInstall(
            recipe: $this->recipe,
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
        self::assertInstanceOf(
            AccountEnvironmentInstall::class,
            $this->accountInstall,
        );
    }

    public function testPrepare(): void
    {
        self::assertInstanceOf(
            EditablePlanInterface::class,
            $this->accountInstall->train(
                $this->createMock(ChefInterface::class),
            )
        );
    }
}
