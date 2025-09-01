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

namespace Teknoo\Space\Infrastructures\Kubernetes\Recipe\Plan;

use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateNamespace;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\PrepareAccountErrorHandler;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Registry\CreateRegistryDeployment;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Registry\CreateStorage;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Recipe\Step\AccountCluster\LoadAccountClusters;
use Teknoo\Space\Recipe\Step\AccountRegistry\PersistRegistryCredential;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountRegistryInstall implements EditablePlanInterface
{
    use EditablePlanTrait;

    public function __construct(
        RecipeInterface $recipe,
        private readonly LoadAccountClusters $loadAccountClusters,
        private readonly CreateNamespace $createNamespace,
        private readonly CreateStorage $createStorage,
        private readonly CreateRegistryDeployment $createRegistryAccount,
        private readonly PersistRegistryCredential $persistRegistryCredential,
        private readonly PrepareAccountErrorHandler $errorHandler,
        private readonly ObjectAccessControlInterface $objectAccessControl,
        string $defaultStorageSizeToClaim,
    ) {
        $this->fill($recipe);
        $this->addToWorkplan('storageSizeToClaim', $defaultStorageSizeToClaim);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient(ClusterCatalog::class, 'clusterCatalog'));
        $recipe = $recipe->require(new Ingredient(Account::class));
        $recipe = $recipe->require(new Ingredient(AccountHistory::class));
        $recipe = $recipe->require(new Ingredient('string', 'accountNamespace'));
        $recipe = $recipe->require(new Ingredient('string', 'storageSizeToClaim'));

        $recipe = $recipe->cook($this->objectAccessControl, ObjectAccessControlInterface::class, [], 10);

        $recipe = $recipe->cook($this->loadAccountClusters, LoadAccountClusters::class, [], 15);

        $recipe = $recipe->cook($this->createNamespace, CreateNamespace::class, [], 20);

        $recipe = $recipe->cook($this->createStorage, CreateStorage::class, [], 40);

        $recipe = $recipe->cook($this->createRegistryAccount, CreateRegistryDeployment::class, [], 40);

        $recipe = $recipe->cook($this->persistRegistryCredential, PersistRegistryCredential::class, [], 50);

        $recipe = $recipe->onError(new Bowl($this->errorHandler, []));

        $this->addToWorkplan('forRegistry', true);

        return $recipe;
    }
}
