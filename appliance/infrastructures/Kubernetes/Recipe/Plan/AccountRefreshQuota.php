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

use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Recipe\Step\EndLooping;
use Teknoo\East\Common\Recipe\Step\JumpIf;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\StartLoopingOn;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReinstallAccountErrorHandler;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReloadNamespace;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateQuota;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Client\SetRedirectClientAtEnd;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Recipe\Plan\Traits\PrepareAccountTrait;
use Teknoo\Space\Recipe\Step\Account\PrepareRedirection;
use Teknoo\Space\Recipe\Step\Account\UpdateAccountHistory;
use Teknoo\Space\Recipe\Step\AccountCluster\LoadAccountClusters;
use Teknoo\Space\Recipe\Step\AccountEnvironment\LoadEnvironments;
use Teknoo\Space\Recipe\Step\AccountEnvironment\ReloadEnvironement;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;
use Teknoo\Space\Recipe\Step\ClusterConfig\SelectClusterConfig;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountRefreshQuota implements EditablePlanInterface
{
    use EditablePlanTrait;
    use PrepareAccountTrait;

    public function __construct(
        RecipeInterface $recipe,
        private readonly LoadObject $loadObject,
        private readonly PrepareRedirection $prepareRedirection,
        private readonly SetRedirectClientAtEnd $redirectClient,
        private readonly LoadHistory $loadHistory,
        private readonly LoadEnvironments $loadEnvironments,
        private readonly LoadAccountClusters $loadAccountClusters,
        private readonly ReloadNamespace $reloadNamespace,
        private readonly ReloadEnvironement $reloadEnvironement,
        private readonly SelectClusterConfig $selectClusterConfig,
        private readonly CreateQuota $createQuota,
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

        $recipe = $this->prepareRecipeForAccount($recipe);

        $recipe = $recipe->cook($this->reloadNamespace, ReloadNamespace::class, [], 70);

        $recipe = $recipe->cook(
            action: new StartLoopingOn(),
            name: StartLoopingOn::class,
            with: [
                'collection' => AccountWallet::class,
            ],
            position: 75
        );

        $recipe = $recipe->cook($this->reloadEnvironement, ReloadEnvironement::class, [], 80);

        $recipe = $recipe->cook($this->selectClusterConfig, SelectClusterConfig::class, [], 90);

        $recipe = $recipe->cook($this->createQuota, CreateQuota::class, [], 100);

        $recipe = $recipe->cook(new EndLooping(), EndLooping::class, [], 110);

        $recipe = $recipe->cook($this->updateAccountHistory, UpdateAccountHistory::class, [], 120);

        $recipe = $recipe->onError(new Bowl($this->errorHandler, []));

        return $recipe;
    }
}
