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

namespace Teknoo\Space\Infrastructures\AnsibleDockerCompose\Recipe\Plan;

use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Bowl\RecipeBowl;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReinstallAccountErrorHandler;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Recipe\Step\AccountEnvironment\FindEnvironmentInWallet;
use Teknoo\Space\Recipe\Step\AccountEnvironment\RemoveEnvironment;

/**
 * Docker-compose environment reinstall: remove the persisted environment then re-run the docker-compose
 * {@see AccountEnvironmentInstall}. Mirrors the Kubernetes reinstall but drops the K8s-only steps —
 * **zero Kubernetes API calls**. Executed in the `new_task` worker through the
 * `Teknoo\Space\Recipe\Plan\Task\AccountProvisioningTask` plan, which loads the account, its history,
 * clusters, environments and registry before delegating here.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountEnvironmentReinstall implements EditablePlanInterface
{
    use EditablePlanTrait;

    public function __construct(
        RecipeInterface $recipe,
        private readonly FindEnvironmentInWallet $findEnvironmentInWallet,
        private readonly RemoveEnvironment $removeEnvironment,
        private readonly AccountEnvironmentInstall $accountEnvironmentInstall,
        private readonly ReinstallAccountErrorHandler $errorHandler,
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient(ClusterCatalog::class, 'clusterCatalog'));
        $recipe = $recipe->require(new Ingredient(Account::class));
        $recipe = $recipe->require(new Ingredient(AccountHistory::class));
        $recipe = $recipe->require(new Ingredient(AccountWallet::class));
        $recipe = $recipe->require(new Ingredient('string', 'accountNamespace'));
        $recipe = $recipe->require(new Ingredient('string', 'envName'));
        $recipe = $recipe->require(new Ingredient('string', 'clusterName'));

        $recipe = $recipe->cook($this->findEnvironmentInWallet, FindEnvironmentInWallet::class, [], 80);

        $recipe = $recipe->cook($this->removeEnvironment, RemoveEnvironment::class, [], 90);

        $recipe = $recipe->cook(
            new RecipeBowl($this->accountEnvironmentInstall, 0),
            AccountEnvironmentInstall::class,
            [],
            100
        );

        return $recipe->onError(new Bowl($this->errorHandler, []));
    }
}
