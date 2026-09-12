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
use Teknoo\Space\Infrastructures\AnsibleDockerCompose\Recipe\Step\PersistSshIdentity;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\PrepareAccountErrorHandler;
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
    private AccountEnvironmentInstall $accountEnvironmentInstall;

    private RecipeInterface&Stub $recipe;

    private LoadAccountClusters&Stub $loadAccountClusters;

    private SelectClusterConfig&Stub $selectClusterConfig;

    private PersistSshIdentity&Stub $persistSshIdentity;

    private PersistEnvironment&Stub $persistCredentials;

    private PrepareAccountErrorHandler&Stub $errorHandler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createStub(RecipeInterface::class);
        $this->loadAccountClusters = $this->createStub(LoadAccountClusters::class);
        $this->selectClusterConfig = $this->createStub(SelectClusterConfig::class);
        $this->persistSshIdentity = $this->createStub(PersistSshIdentity::class);
        $this->persistCredentials = $this->createStub(PersistEnvironment::class);
        $this->errorHandler = $this->createStub(PrepareAccountErrorHandler::class);

        $this->accountEnvironmentInstall = new AccountEnvironmentInstall(
            recipe: $this->recipe,
            loadAccountClusters: $this->loadAccountClusters,
            selectClusterConfig: $this->selectClusterConfig,
            persistSshIdentity: $this->persistSshIdentity,
            persistCredentials: $this->persistCredentials,
            errorHandler: $this->errorHandler,
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            AccountEnvironmentInstall::class,
            $this->accountEnvironmentInstall,
        );
    }

    public function testPrepare(): void
    {
        $this->assertInstanceOf(
            EditablePlanInterface::class,
            $this->accountEnvironmentInstall->train(
                $this->createStub(ChefInterface::class),
            )
        );
    }
}
