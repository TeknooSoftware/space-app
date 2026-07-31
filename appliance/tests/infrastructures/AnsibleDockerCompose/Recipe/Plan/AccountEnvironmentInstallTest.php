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
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
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
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountEnvironmentInstall::class)]
class AccountEnvironmentInstallTest extends TestCase
{
    private AccountEnvironmentInstall $plan;

    private RecipeInterface&Stub $recipe;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->createStub(RecipeInterface::class);

        $this->plan = new AccountEnvironmentInstall(
            recipe: $this->recipe,
            loadAccountClusters: $this->createStub(LoadAccountClusters::class),
            selectClusterConfig: $this->createStub(SelectClusterConfig::class),
            persistSshIdentity: $this->createStub(PersistSshIdentity::class),
            persistCredentials: $this->createStub(PersistEnvironment::class),
            errorHandler: $this->createStub(PrepareAccountErrorHandler::class),
            objectAccessControl: $this->createStub(ObjectAccessControlInterface::class),
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(AccountEnvironmentInstall::class, $this->plan);
    }

    public function testPrepare(): void
    {
        $this->assertInstanceOf(
            EditablePlanInterface::class,
            $this->plan->train($this->createStub(ChefInterface::class)),
        );
    }
}
