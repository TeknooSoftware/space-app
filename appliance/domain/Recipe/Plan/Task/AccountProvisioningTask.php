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
use Teknoo\Space\Object\DTO\Task\AbstractAccountTask;
use Teknoo\Space\Recipe\Step\Account\UpdateAccountHistory;
use Teknoo\Space\Recipe\Step\AccountCluster\LoadAccountClusters;
use Teknoo\Space\Recipe\Step\AccountEnvironment\LoadEnvironments;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;
use Teknoo\Space\Recipe\Step\AccountRegistry\LoadRegistryCredential;
use Teknoo\Space\Recipe\Step\Task\AccountTaskErrorHandler;

/**
 * Worker-side plan of an account provisioning task, reached through the `NewTaskRecipeRegistry` when one of
 * the `Teknoo\Space\Object\DTO\Task\*` tasks is consumed off the `new_task` transport. One instance is
 * configured per task class, with the type-dispatch `ProvisioningPlanBowl` of the matching role.
 *
 * The task only carries identifiers, so this plan rebuilds the workplan the provisioning plans expect:
 * the account (under the `Account` key, required by the sub-plans' ingredients), its history, the cluster
 * catalog extended with the account's own clusters — it must be loaded *before* the bowl, which resolves the
 * cluster type from it —, then, per role, the environments wallet and the registry credential, and the
 * account namespace. The history is persisted at the end whatever happened in the sub-plan.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountProvisioningTask implements EditablePlanInterface
{
    use EditablePlanTrait;

    /**
     * @param class-string<AbstractAccountTask> $taskClass
     * @param LoaderInterface<Account> $accountLoader
     */
    public function __construct(
        RecipeInterface $recipe,
        private readonly string $taskClass,
        private readonly LoadObject $loadObject,
        LoaderInterface $accountLoader,
        ClusterCatalog $clusterCatalog,
        private readonly LoadHistory $loadHistory,
        private readonly LoadAccountClusters $loadAccountClusters,
        private readonly ReloadNamespace $reloadNamespace,
        private readonly BowlInterface $provisioningBowl,
        private readonly UpdateAccountHistory $updateAccountHistory,
        private readonly AccountTaskErrorHandler $errorHandler,
        private readonly ?LoadEnvironments $loadEnvironments = null,
        private readonly ?LoadRegistryCredential $loadRegistryCredential = null,
    ) {
        $this->fill($recipe);

        $this->addToWorkplan('loader', $accountLoader);
        $this->addToWorkplan('clusterCatalog', $clusterCatalog);
        $this->addToWorkplan('allowEmptyCredentials', true);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient($this->taskClass));
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

        if (null !== $this->loadEnvironments) {
            $recipe = $recipe->cook($this->loadEnvironments, LoadEnvironments::class, [], 35);
        }

        if (null !== $this->loadRegistryCredential) {
            $recipe = $recipe->cook($this->loadRegistryCredential, LoadRegistryCredential::class, [], 35);
        }

        $recipe = $recipe->cook($this->reloadNamespace, ReloadNamespace::class, [], 40);

        $recipe = $recipe->cook($this->provisioningBowl, 'provisioning', [], 50);

        $recipe = $recipe->cook($this->updateAccountHistory, UpdateAccountHistory::class, [], 60);

        return $recipe->onError(new Bowl($this->errorHandler, []));
    }
}
