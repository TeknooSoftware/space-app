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

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Bowl\RecipeBowl;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\Cookbook\BaseCookbookTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReinstallAccountErrorHandler;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Client\SetRedirectClientAtEnd;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Recipe\Cookbook\Traits\PrepareAccountTrait;
use Teknoo\Space\Recipe\Step\AccountCredential\LoadCredentials;
use Teknoo\Space\Recipe\Step\AccountCredential\RemoveCredentials;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;
use Teknoo\Space\Recipe\Step\Account\PrepareRedirection;
use Teknoo\Space\Recipe\Step\Account\SetAccountNamespace;
use Teknoo\Space\Recipe\Step\Account\UpdateAccountHistory;
use Teknoo\Space\Recipe\Step\AccountRegistry\LoadRegistryCredentials;
use Teknoo\Space\Recipe\Step\AccountRegistry\RemoveRegistryCredentials;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountReinstall implements CookbookInterface
{
    use BaseCookbookTrait;
    use PrepareAccountTrait;

    public function __construct(
        RecipeInterface $recipe,
        private LoadObject $loadObject,
        private PrepareRedirection $prepareRedirection,
        private SetRedirectClientAtEnd $redirectClient,
        private LoadHistory $loadHistory,
        private LoadCredentials $loadCredentials,
        private LoadRegistryCredentials $loadRegistryCredentials,
        private RemoveCredentials $removeCredentials,
        private RemoveRegistryCredentials $removeRegistryCredentials,
        private SetAccountNamespace $setAccountNamespace,
        private AccountInstall $installAccount,
        private UpdateAccountHistory $updateAccountHistory,
        private ReinstallAccountErrorHandler $errorHandler,
        private ObjectAccessControlInterface $objectAccessControl,
        string $defaultStorageSizeToClaim,
    ) {
        $this->fill($recipe);
        $this->addToWorkplan('storageSizeToClaim', $defaultStorageSizeToClaim);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient(ServerRequestInterface::class, 'request'));
        $recipe = $recipe->require(new Ingredient(LoaderInterface::class, 'loader'));
        $recipe = $recipe->require(new Ingredient('string', 'id'));
        $recipe = $recipe->require(new Ingredient('string', 'storageSizeToClaim'));
        $recipe = $recipe->require(new Ingredient(ClusterCatalog::class, 'clusterCatalog'));

        $recipe = $this->prepareRecipeForAccount($recipe);

        $recipe = $recipe->cook($this->removeCredentials, RemoveCredentials::class, [], 70);

        $recipe = $recipe->cook($this->removeRegistryCredentials, RemoveRegistryCredentials::class, [], 70);

        $recipe = $recipe->cook($this->setAccountNamespace, SetAccountNamespace::class, [], 80);

        $recipe = $recipe->cook(new RecipeBowl($this->installAccount, 0), AccountInstall::class, [], 90);

        $recipe = $recipe->cook($this->updateAccountHistory, UpdateAccountHistory::class, [], 100);

        $recipe = $recipe->onError(new Bowl($this->errorHandler, []));

        return $recipe;
    }
}
