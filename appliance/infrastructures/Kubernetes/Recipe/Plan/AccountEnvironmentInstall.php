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
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
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
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateDockerSecret;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateQuota;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateRole;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateRoleBinding;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateSecretServiceAccountToken;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateServiceAccount;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Object\Persisted\AccountRegistry;
use Teknoo\Space\Recipe\Step\AccountEnvironment\PersistEnvironment;
use Teknoo\Space\Recipe\Step\ClusterConfig\SelectClusterConfig;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountEnvironmentInstall implements EditablePlanInterface
{
    use EditablePlanTrait;

    public function __construct(
        RecipeInterface $recipe,
        private readonly CreateNamespace $createNamespace,
        private readonly SelectClusterConfig $selectClusterConfig,
        private readonly CreateServiceAccount $createServiceAccount,
        private readonly CreateQuota $createQuota,
        private readonly CreateRole $createRole,
        private readonly CreateRoleBinding $createRoleBinding,
        private readonly CreateDockerSecret $createDockerSecret,
        private readonly CreateSecretServiceAccountToken $createSecret,
        private readonly PersistEnvironment $persistCredentials,
        private readonly PrepareAccountErrorHandler $errorHandler,
        private readonly ObjectAccessControlInterface $objectAccessControl,
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient(ClusterCatalog::class, 'clusterCatalog'));
        $recipe = $recipe->require(new Ingredient(Account::class));
        $recipe = $recipe->require(new Ingredient(AccountHistory::class));
        $recipe = $recipe->require(new Ingredient(AccountRegistry::class));
        $recipe = $recipe->require(new Ingredient('string', 'accountNamespace'));
        $recipe = $recipe->require(new Ingredient('string', 'envName'));
        $recipe = $recipe->require(new Ingredient('string', 'clusterName'));

        $recipe = $recipe->cook($this->objectAccessControl, ObjectAccessControlInterface::class, [], 10);

        $recipe = $recipe->cook($this->createNamespace, CreateNamespace::class, [], 20);

        $recipe = $recipe->cook($this->selectClusterConfig, SelectClusterConfig::class, [], 30);

        $recipe = $recipe->cook($this->createServiceAccount, CreateServiceAccount::class, [], 40);

        $recipe = $recipe->cook($this->createQuota, CreateQuota::class, [], 50);

        $recipe = $recipe->cook($this->createRole, CreateRole::class, [], 60);

        $recipe = $recipe->cook($this->createRoleBinding, CreateRoleBinding::class, [], 70);

        $recipe = $recipe->cook($this->createDockerSecret, CreateDockerSecret::class, [], 80);

        $recipe = $recipe->cook($this->createSecret, CreateSecretServiceAccountToken::class, [], 90);

        $recipe = $recipe->cook($this->persistCredentials, PersistEnvironment::class, [], 100);

        $recipe = $recipe->onError(new Bowl($this->errorHandler, []));

        $this->addToWorkplan('forRegistry', false);

        return $recipe;
    }
}
