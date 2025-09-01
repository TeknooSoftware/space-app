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

namespace Teknoo\Space\Recipe\Plan;

use Stringable;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Recipe\Step\Account\LoadSubscriptionPlan;
use Teknoo\Space\Recipe\Step\AccountEnvironment\CreateResumes;
use Teknoo\Space\Recipe\Step\AccountEnvironment\LoadEnvironments;
use Teknoo\Space\Recipe\Step\Subscription\InjectStatus;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AdminAccountStatus extends AccountStatus
{
    public function __construct(
        RecipeInterface $recipe,
        private readonly LoadObject $loadObject,
        LoadSubscriptionPlan $loadSubscriptionPlan,
        LoadEnvironments $loadEnvironments,
        CreateResumes $createResumes,
        InjectStatus $injectStatus,
        Render $render,
        RenderError $renderError,
        string|Stringable $defaultErrorTemplate,
    ) {
        parent::__construct(
            recipe: $recipe,
            loadSubscriptionPlan: $loadSubscriptionPlan,
            loadEnvironments: $loadEnvironments,
            createResumes: $createResumes,
            injectStatus: $injectStatus,
            render: $render,
            renderError: $renderError,
            defaultErrorTemplate: $defaultErrorTemplate,
        );
    }

    #[\Override]
    protected function populateRecipe(RecipeInterface $recipe, bool $skipRequires = false): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient('string', 'id'));
        $recipe = $recipe->require(new Ingredient(LoaderInterface::class));

        $recipe = $recipe->cook(
            $this->loadObject,
            LoadObject::class . ':Account',
            [
                'loader' => 'accountLoader',
                'workPlanKey' => 'accountKey'
            ],
            05
        );

        return parent::populateRecipe(
            recipe: $recipe,
            skipRequires: true,
        );
    }
}
