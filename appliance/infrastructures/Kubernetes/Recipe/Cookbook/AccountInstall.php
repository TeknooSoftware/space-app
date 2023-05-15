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

namespace Teknoo\Space\Infrastructures\Kubernetes\Recipe\Cookbook;

use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\Cookbook\BaseCookbookTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateNamespace;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateRegistryAccount;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateRole;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateRoleBinding;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateSecretServiceAccountToken;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateServiceAccount;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateStorage;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\PrepareAccountErrorHandler;
use Teknoo\Space\Recipe\Step\AccountCredential\PersistCredentials;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountInstall implements CookbookInterface
{
    use BaseCookbookTrait;

    public function __construct(
        RecipeInterface $recipe,
        private CreateNamespace $createNamespace,
        private CreateServiceAccount $createServiceAccount,
        private CreateRole $createRole,
        private CreateRoleBinding $createRoleBinding,
        private CreateSecretServiceAccountToken $createSecret,
        private CreateStorage $createStorage,
        private CreateRegistryAccount $createRegistryAccount,
        private PersistCredentials $persistCredentials,
        private PrepareAccountErrorHandler $errorHandler,
        string $defaultStorageSizeToClaim,
    ) {
        $this->fill($recipe);
        $this->addToWorkplan('storageSizeToClaim', $defaultStorageSizeToClaim);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient('string', 'accountNamespace'));
        $recipe = $recipe->require(new Ingredient('string', 'storageSizeToClaim'));

        $recipe = $recipe->cook($this->createNamespace, CreateNamespace::class, [], 0);

        $recipe = $recipe->cook($this->createServiceAccount, CreateServiceAccount::class, [], 10);

        $recipe = $recipe->cook($this->createRole, CreateRole::class, [], 20);

        $recipe = $recipe->cook($this->createRoleBinding, CreateRoleBinding::class, [], 30);

        $recipe = $recipe->cook($this->createSecret, CreateSecretServiceAccountToken::class, [], 40);

        $recipe = $recipe->cook($this->createStorage, CreateStorage::class, [], 50);

        $recipe = $recipe->cook($this->createRegistryAccount, CreateRegistryAccount::class, [], 60);

        $recipe = $recipe->cook($this->persistCredentials, PersistCredentials::class, [], 70);

        $recipe = $recipe->onError(new Bowl($this->errorHandler, []));

        return $recipe;
    }
}
