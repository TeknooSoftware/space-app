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

use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Infrastructures\AnsibleDockerCompose\Recipe\Step\PersistSshIdentity;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\PrepareAccountErrorHandler;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Recipe\Step\AccountCluster\LoadAccountClusters;
use Teknoo\Space\Recipe\Step\AccountEnvironment\PersistEnvironment;
use Teknoo\Space\Recipe\Step\ClusterConfig\SelectClusterConfig;

/**
 * Docker-compose environment provisioning. Unlike the Kubernetes plan there is no namespace/service-account/
 * role/quota to mint: provisioning persists the admin-supplied SSH identity + the compose namespace onto the
 * `AccountEnvironment`. The SSH private key is supplied, never generated. **Zero Kubernetes API calls.**
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountEnvironmentInstall implements EditablePlanInterface
{
    use EditablePlanTrait;

    public function __construct(
        RecipeInterface $recipe,
        private readonly LoadAccountClusters $loadAccountClusters,
        private readonly SelectClusterConfig $selectClusterConfig,
        private readonly PersistSshIdentity $persistSshIdentity,
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
        $recipe = $recipe->require(new Ingredient('string', 'accountNamespace'));
        $recipe = $recipe->require(new Ingredient('string', 'envName'));
        $recipe = $recipe->require(new Ingredient('string', 'clusterName'));

        $recipe = $recipe->cook($this->objectAccessControl, ObjectAccessControlInterface::class, [], 10);

        $recipe = $recipe->cook($this->loadAccountClusters, LoadAccountClusters::class, [], 15);

        $recipe = $recipe->cook($this->selectClusterConfig, SelectClusterConfig::class, [], 30);

        $recipe = $recipe->cook($this->persistSshIdentity, PersistSshIdentity::class, [], 40);

        $recipe = $recipe->cook($this->persistCredentials, PersistEnvironment::class, [], 100);

        return $recipe->onError(new Bowl($this->errorHandler, []));
    }
}
