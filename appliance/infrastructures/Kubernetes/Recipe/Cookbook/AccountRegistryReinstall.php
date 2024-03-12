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
use Teknoo\East\Common\Recipe\Step\EndLooping;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\StartLoopingOn;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\Cookbook\BaseCookbookTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateRegistryDeployment;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateStorage;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReinstallAccountErrorHandler;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReloadNamespace;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Client\SetRedirectClientAtEnd;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Recipe\Cookbook\Traits\PrepareAccountTrait;
use Teknoo\Space\Recipe\Step\AccountCredential\LoadCredentials;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;
use Teknoo\Space\Recipe\Step\Account\PrepareRedirection;
use Teknoo\Space\Recipe\Step\Account\UpdateAccountHistory;
use Teknoo\Space\Recipe\Step\AccountRegistry\LoadRegistryCredentials;
use Teknoo\Space\Recipe\Step\AccountRegistry\PersistRegistryCredentials;
use Teknoo\Space\Recipe\Step\AccountRegistry\RemoveRegistryCredentials;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountRegistryReinstall implements CookbookInterface
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
        private ReloadNamespace $reloadNamespace,
        private RemoveRegistryCredentials $removeRegistryCredentials,
        private CreateStorage $createStorage,
        private CreateRegistryDeployment $createRegistryAccount,
        private PersistRegistryCredentials $persistRegistryCredentials,
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
        $recipe = $recipe->require(new Ingredient(ClusterCatalog::class, 'clusterCatalog'));

        $recipe = $this->prepareRecipeForAccount($recipe);

        $recipe = $recipe->cook($this->reloadNamespace, ReloadNamespace::class, [], 70);

        $recipe = $recipe->cook($this->removeRegistryCredentials, RemoveRegistryCredentials::class, [], 80);

        $recipe = $recipe->cook(
            action: new StartLoopingOn(),
            name: StartLoopingOn::class,
            with: [
                'collection' => 'clusterCatalog'
            ],
            position: 85
        );

        $recipe = $recipe->cook($this->createStorage, CreateStorage::class, [], 90);

        $recipe = $recipe->cook($this->createRegistryAccount, CreateRegistryDeployment::class, [], 100);

        $recipe = $recipe->cook(new EndLooping(), EndLooping::class, [], 105);

        $recipe = $recipe->cook($this->persistRegistryCredentials, PersistRegistryCredentials::class, [], 110);

        $recipe = $recipe->cook($this->updateAccountHistory, UpdateAccountHistory::class, [], 120);

        $recipe = $recipe->onError(new Bowl($this->errorHandler, []));

        return $recipe;
    }
}
