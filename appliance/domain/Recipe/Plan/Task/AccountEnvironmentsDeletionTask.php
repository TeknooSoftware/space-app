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
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Recipe\Value;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\DeleteNamespaces;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\Task\DeleteEnvironmentsTask;
use Teknoo\Space\Recipe\Step\Account\UpdateAccountHistory;
use Teknoo\Space\Recipe\Step\AccountCluster\LoadAccountClusters;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;
use Teknoo\Space\Recipe\Step\Task\AccountTaskErrorHandler;

/**
 * Worker-side plan of a `DeleteEnvironmentsTask`, reached through the `NewTaskRecipeRegistry` when the
 * task is consumed off the `new_task` transport. The removed `AccountEnvironment` documents no longer
 * exist, so the plan only reloads the account (under the `Account` key), its history and the cluster
 * catalog extended with the account's own clusters, then tears down the namespaces listed by the task and
 * persists the history, whatever happened.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountEnvironmentsDeletionTask implements EditablePlanInterface
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
        private readonly DeleteNamespaces $deleteNamespaces,
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
        $recipe = $recipe->require(new Ingredient(DeleteEnvironmentsTask::class));
        $recipe = $recipe->require(new Ingredient('string', 'accountId'));
        $recipe = $recipe->require(new Ingredient('array', 'environmentsToDelete'));

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

        $recipe = $recipe->cook($this->deleteNamespaces, DeleteNamespaces::class, [], 50);

        $recipe = $recipe->cook($this->updateAccountHistory, UpdateAccountHistory::class, [], 60);

        return $recipe->onError(new Bowl($this->errorHandler, []));
    }
}
