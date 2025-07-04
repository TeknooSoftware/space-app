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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Recipe\Plan;

use Stringable;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Recipe\Step\Account\LoadSubscriptionPlan;
use Teknoo\Space\Recipe\Step\AccountEnvironment\CreateResumes;
use Teknoo\Space\Recipe\Step\AccountEnvironment\LoadEnvironments;
use Teknoo\Space\Recipe\Step\Subscription\InjectStatus;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountStatus implements EditablePlanInterface
{
    use EditablePlanTrait;

    public function __construct(
        RecipeInterface $recipe,
        private readonly LoadSubscriptionPlan $loadSubscriptionPlan,
        private readonly LoadEnvironments $loadEnvironments,
        private readonly CreateResumes $createResumes,
        private readonly InjectStatus $injectStatus,
        private readonly Render $render,
        private readonly RenderError $renderError,
        private readonly string|Stringable $defaultErrorTemplate,
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe, bool $skipRequires = false): RecipeInterface
    {
        if (false === $skipRequires) {
            $recipe = $recipe->require(new Ingredient(SpaceAccount::class));
        }

        $recipe = $recipe->cook($this->loadSubscriptionPlan, LoadSubscriptionPlan::class, [], 10);

        $recipe = $recipe->cook($this->loadEnvironments, LoadEnvironments::class, [], 10);

        $recipe = $recipe->cook($this->createResumes, CreateResumes::class, [], 20);

        $recipe = $recipe->cook($this->injectStatus, InjectStatus::class, [], 40);

        $recipe = $recipe->cook($this->render, Render::class, [], 50);

        $recipe = $recipe->onError(new Bowl($this->renderError, []));

        $this->addToWorkplan('errorTemplate', (string) $this->defaultErrorTemplate);
        $this->addToWorkplan('allowEmptyCredentials', true);

        return $recipe;
    }
}
