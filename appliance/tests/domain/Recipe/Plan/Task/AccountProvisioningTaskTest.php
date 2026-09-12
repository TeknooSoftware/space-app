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

namespace Teknoo\Space\Tests\Unit\Recipe\Plan\Task;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReloadNamespace;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\Task\InstallRegistryTask;
use Teknoo\Space\Object\DTO\Task\ReinstallEnvironmentTask;
use Teknoo\Space\Recipe\Plan\Task\AccountProvisioningTask;
use Teknoo\Space\Recipe\Step\Account\UpdateAccountHistory;
use Teknoo\Space\Recipe\Step\AccountCluster\LoadAccountClusters;
use Teknoo\Space\Recipe\Step\AccountEnvironment\LoadEnvironments;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;
use Teknoo\Space\Recipe\Step\AccountRegistry\LoadRegistryCredential;
use Teknoo\Space\Recipe\Step\Task\AccountTaskErrorHandler;

/**
 * Class AccountProvisioningTaskTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(AccountProvisioningTask::class)]
class AccountProvisioningTaskTest extends TestCase
{
    private function buildPlan(bool $withOptionalLoaders): AccountProvisioningTask
    {
        return new AccountProvisioningTask(
            recipe: $this->createStub(RecipeInterface::class),
            taskClass: $withOptionalLoaders ? ReinstallEnvironmentTask::class : InstallRegistryTask::class,
            loadObject: $this->createStub(LoadObject::class),
            accountLoader: $this->createStub(LoaderInterface::class),
            clusterCatalog: $this->createStub(ClusterCatalog::class),
            loadHistory: $this->createStub(LoadHistory::class),
            loadAccountClusters: $this->createStub(LoadAccountClusters::class),
            reloadNamespace: $this->createStub(ReloadNamespace::class),
            provisioningBowl: $this->createStub(BowlInterface::class),
            updateAccountHistory: $this->createStub(UpdateAccountHistory::class),
            errorHandler: $this->createStub(AccountTaskErrorHandler::class),
            loadEnvironments: $withOptionalLoaders ? $this->createStub(LoadEnvironments::class) : null,
            loadRegistryCredential: $withOptionalLoaders ? $this->createStub(LoadRegistryCredential::class) : null,
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            AccountProvisioningTask::class,
            $this->buildPlan(false),
        );
    }

    public function testPrepareWithoutOptionalLoaders(): void
    {
        $this->assertInstanceOf(
            EditablePlanInterface::class,
            $this->buildPlan(false)->train(
                $this->createStub(ChefInterface::class),
            )
        );
    }

    public function testPrepareWithOptionalLoaders(): void
    {
        $this->assertInstanceOf(
            EditablePlanInterface::class,
            $this->buildPlan(true)->train(
                $this->createStub(ChefInterface::class),
            )
        );
    }
}
