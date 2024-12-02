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
 * @link        https://teknoo.software/applications/space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Infrastructures\Kubernetes\Recipe\Plan;

use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Recipe\Step\JumpIf;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Bowl\RecipeBowl;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReinstallAccountErrorHandler;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReloadNamespace;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Client\SetRedirectClientAtEnd;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Recipe\Plan\Traits\PrepareAccountTrait;
use Teknoo\Space\Recipe\Step\AccountEnvironment\FindEnvironmentInWallet;
use Teknoo\Space\Recipe\Step\AccountEnvironment\LoadEnvironments;
use Teknoo\Space\Recipe\Step\AccountEnvironment\RemoveEnvironment;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;
use Teknoo\Space\Recipe\Step\Account\PrepareRedirection;
use Teknoo\Space\Recipe\Step\Account\UpdateAccountHistory;
use Teknoo\Space\Recipe\Step\AccountRegistry\LoadRegistryCredential;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountEnvironmentReinstall implements EditablePlanInterface
{
    use EditablePlanTrait;
    use PrepareAccountTrait;

    public function __construct(
        RecipeInterface $recipe,
        private readonly LoadObject $loadObject,
        private readonly PrepareRedirection $prepareRedirection,
        private readonly SetRedirectClientAtEnd $redirectClient,
        private readonly LoadHistory $loadHistory,
        private readonly LoadEnvironments $loadCredentials,
        private readonly LoadRegistryCredential $loadRegistryCredential,
        private readonly ReloadNamespace $reloadNamespace,
        private readonly FindEnvironmentInWallet $findEnvironmentInWallet,
        private readonly RemoveEnvironment $removeEnvironment,
        private readonly AccountEnvironmentInstall $accountEnvironmentInstall,
        private readonly UpdateAccountHistory $updateAccountHistory,
        private readonly JumpIf $jumpIf,
        private readonly Render $render,
        private readonly ReinstallAccountErrorHandler $errorHandler,
        private readonly ObjectAccessControlInterface $objectAccessControl,
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient(LoaderInterface::class, 'loader'));
        $recipe = $recipe->require(new Ingredient(ClusterCatalog::class, 'clusterCatalog'));
        $recipe = $recipe->require(new Ingredient('string', 'id'));
        $recipe = $recipe->require(new Ingredient('string', 'envName'));
        $recipe = $recipe->require(new Ingredient('string', 'clusterName'));

        $recipe = $this->prepareRecipeForAccount($recipe);

        $recipe = $recipe->cook($this->reloadNamespace, ReloadNamespace::class, [], 70);

        $recipe = $recipe->cook($this->findEnvironmentInWallet, FindEnvironmentInWallet::class, [], 80);

        $recipe = $recipe->cook($this->removeEnvironment, RemoveEnvironment::class, [], 90);

        $recipe = $recipe->cook(
            new RecipeBowl($this->accountEnvironmentInstall, 0),
            AccountEnvironmentInstall::class,
            [],
            100
        );

        $recipe = $recipe->cook($this->updateAccountHistory, UpdateAccountHistory::class, [], 110);

        $recipe = $recipe->onError(new Bowl($this->errorHandler, []));

        return $recipe;
    }
}
