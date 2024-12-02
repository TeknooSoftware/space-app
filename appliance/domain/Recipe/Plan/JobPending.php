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

namespace Teknoo\Space\Recipe\Plan;

use Stringable;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Contracts\Recipe\Step\Job\FetchJobIdFromPendingInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class JobPending implements EditablePlanInterface
{
    use EditablePlanTrait;

    public function __construct(
        RecipeInterface $recipe,
        private readonly LoadObject $loadObject,
        private readonly ObjectAccessControlInterface $objectAccessControl,
        private readonly FetchJobIdFromPendingInterface $fetchJobIdFromPending,
        private readonly Render $render,
        private readonly RenderError $renderError,
        private readonly string|Stringable $defaultErrorTemplate,
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient('string', 'template'));
        $recipe = $recipe->require(new Ingredient('string', 'projectId'));
        $recipe = $recipe->require(new Ingredient('string', 'newJobId'));

        $recipe = $recipe->cook(
            $this->loadObject,
            LoadObject::class . ':Project',
            [
                'loader' => 'projectLoader',
                'id' => 'projectId',
                'workPlanKey' => 'projectKey'
            ],
            05
        );

        $recipe = $recipe->cook(
            $this->objectAccessControl,
            ObjectAccessControlInterface::class,
            [
                'object' => Project::class
            ],
            06
        );

        $recipe = $recipe->cook($this->fetchJobIdFromPending, FetchJobIdFromPendingInterface::class, [], 10);

        $recipe = $recipe->cook($this->render, Render::class, [], 20);

        $recipe = $recipe->onError(new Bowl($this->renderError, []));

        $this->addToWorkplan('nextStep', RenderFormInterface::class);

        $this->addToWorkplan('errorTemplate', (string) $this->defaultErrorTemplate);

        return $recipe;
    }
}
