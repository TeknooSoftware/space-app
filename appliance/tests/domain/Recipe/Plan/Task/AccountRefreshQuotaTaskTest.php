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
use Teknoo\Space\Recipe\Plan\Task\AccountRefreshQuotaTask;
use Teknoo\Space\Recipe\Step\Account\UpdateAccountHistory;
use Teknoo\Space\Recipe\Step\AccountCluster\LoadAccountClusters;
use Teknoo\Space\Recipe\Step\AccountEnvironment\EndLoopingOnWallet;
use Teknoo\Space\Recipe\Step\AccountEnvironment\LoadEnvironments;
use Teknoo\Space\Recipe\Step\AccountEnvironment\ReloadEnvironement;
use Teknoo\Space\Recipe\Step\AccountEnvironment\StartLoopingOnWallet;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;
use Teknoo\Space\Recipe\Step\Task\AccountTaskErrorHandler;

/**
 * Class AccountRefreshQuotaTaskTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(AccountRefreshQuotaTask::class)]
class AccountRefreshQuotaTaskTest extends TestCase
{
    private function buildPlan(): AccountRefreshQuotaTask
    {
        return new AccountRefreshQuotaTask(
            recipe: $this->createStub(RecipeInterface::class),
            loadObject: $this->createStub(LoadObject::class),
            accountLoader: $this->createStub(LoaderInterface::class),
            clusterCatalog: $this->createStub(ClusterCatalog::class),
            loadHistory: $this->createStub(LoadHistory::class),
            loadAccountClusters: $this->createStub(LoadAccountClusters::class),
            loadEnvironments: $this->createStub(LoadEnvironments::class),
            reloadNamespace: $this->createStub(ReloadNamespace::class),
            startLoopingOnWallet: $this->createStub(StartLoopingOnWallet::class),
            reloadEnvironement: $this->createStub(ReloadEnvironement::class),
            provisioningBowl: $this->createStub(BowlInterface::class),
            endLoopingOnWallet: $this->createStub(EndLoopingOnWallet::class),
            updateAccountHistory: $this->createStub(UpdateAccountHistory::class),
            errorHandler: $this->createStub(AccountTaskErrorHandler::class),
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            AccountRefreshQuotaTask::class,
            $this->buildPlan(),
        );
    }

    public function testPrepare(): void
    {
        $this->assertInstanceOf(
            EditablePlanInterface::class,
            $this->buildPlan()->train(
                $this->createStub(ChefInterface::class),
            )
        );
    }
}
