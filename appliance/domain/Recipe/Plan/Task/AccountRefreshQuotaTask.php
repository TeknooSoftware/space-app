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

namespace Teknoo\Space\Recipe\Plan\Task;

use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Recipe\Value;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReloadNamespace;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\Task\RefreshQuotaTask;
use Teknoo\Space\Recipe\Step\Account\UpdateAccountHistory;
use Teknoo\Space\Recipe\Step\AccountCluster\LoadAccountClusters;
use Teknoo\Space\Recipe\Step\AccountEnvironment\EndLoopingOnWallet;
use Teknoo\Space\Recipe\Step\AccountEnvironment\LoadEnvironments;
use Teknoo\Space\Recipe\Step\AccountEnvironment\ReloadEnvironement;
use Teknoo\Space\Recipe\Step\AccountEnvironment\StartLoopingOnWallet;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;
use Teknoo\Space\Recipe\Step\Task\AccountTaskErrorHandler;

/**
 * Worker-side plan of the `RefreshQuotaTask`, reached through the `NewTaskRecipeRegistry` when the task is
 * consumed off the `new_task` transport.
 *
 * Unlike the other provisioning tasks, a quota refresh is not scoped to one environment: it applies to every
 * environment of the account (an environment is a cluster plus a namespace). This plan loads the account (under
 * the `Account` key required by the sub-plans), its history, the cluster catalog extended with the account's own
 * clusters and the environments wallet, then loops over the wallet: for each environment it exposes its
 * `envName` / `clusterName` / `kubeNamespace` and lets the type-dispatch `ProvisioningPlanBowl` pick the
 * Kubernetes or Docker Compose single-environment quota plan for *that* cluster. The history is persisted at the
 * end whatever happened.
 *
 * The loop state is carried by the workplan (`WalletCursor`), never by a step instance, because the same plan
 * instance serves every task consumed by a long-running worker.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountRefreshQuotaTask implements EditablePlanInterface
{
    use EditablePlanTrait;

    /**
     * @param LoaderInterface<Account> $accountLoader
     */
    public function __construct(
        RecipeInterface $recipe,
        private readonly LoadObject $loadObject,
        LoaderInterface $accountLoader,
        ClusterCatalog $clusterCatalog,
        private readonly LoadHistory $loadHistory,
        private readonly LoadAccountClusters $loadAccountClusters,
        private readonly LoadEnvironments $loadEnvironments,
        private readonly ReloadNamespace $reloadNamespace,
        private readonly StartLoopingOnWallet $startLoopingOnWallet,
        private readonly ReloadEnvironement $reloadEnvironement,
        private readonly BowlInterface $provisioningBowl,
        private readonly EndLoopingOnWallet $endLoopingOnWallet,
        private readonly UpdateAccountHistory $updateAccountHistory,
        private readonly AccountTaskErrorHandler $errorHandler,
    ) {
        $this->fill($recipe);

        $this->addToWorkplan('loader', $accountLoader);
        $this->addToWorkplan('clusterCatalog', $clusterCatalog);
        $this->addToWorkplan('allowEmptyCredentials', true);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient(RefreshQuotaTask::class));
        $recipe = $recipe->require(new Ingredient('string', 'accountId'));

        $recipe = $recipe->cook(
            $this->loadObject,
            LoadObject::class,
            [
                'id' => 'accountId',
                'workPlanKey' => new Value(Account::class),
            ],
            10,
        );

        $recipe = $recipe->cook($this->loadHistory, LoadHistory::class, [], 20);

        $recipe = $recipe->cook($this->loadAccountClusters, LoadAccountClusters::class, [], 30);

        $recipe = $recipe->cook($this->loadEnvironments, LoadEnvironments::class, [], 35);

        $recipe = $recipe->cook($this->reloadNamespace, ReloadNamespace::class, [], 40);

        $recipe = $recipe->cook($this->startLoopingOnWallet, StartLoopingOnWallet::class, [], 45);

        $recipe = $recipe->cook($this->reloadEnvironement, ReloadEnvironement::class, [], 46);

        $recipe = $recipe->cook($this->provisioningBowl, 'provisioning', [], 50);

        $recipe = $recipe->cook($this->endLoopingOnWallet, EndLoopingOnWallet::class, [], 55);

        $recipe = $recipe->cook($this->updateAccountHistory, UpdateAccountHistory::class, [], 60);

        return $recipe->onError(new Bowl($this->errorHandler, []));
    }
}
