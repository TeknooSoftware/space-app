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
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Recipe\Step\JumpIf;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\Stop;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\Ingredient\IngredientWithCondition;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Recipe\Value;
use Teknoo\Space\Contracts\Recipe\Step\Task\CallNewTaskInterface;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Client\SetRedirectClientAtEnd;
use Teknoo\Space\Recipe\Step\Account\PrepareRedirection;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;
use Teknoo\Space\Recipe\Step\Task\AddTaskToHistory;
use Teknoo\Space\Recipe\Step\Task\PrepareAccountTask;

/**
 * HTTP entry point (web and API) of the admin account provisioning actions: environment reinstall, registry
 * reinstall and quota refresh. The request loads the account, checks the access, queues the task named by the
 * route's `taskClass` default to the `new_task` worker, records it in the account history, then redirects
 * to the account page (web) or renders the `done` template (API). The provisioning itself happens later in
 * the worker, through the `Teknoo\Space\Recipe\Plan\Task\AccountProvisioningTask` plan.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountTaskDispatch implements EditablePlanInterface
{
    use EditablePlanTrait;

    public function __construct(
        RecipeInterface $recipe,
        private readonly LoadObject $loadObject,
        private readonly ObjectAccessControlInterface $objectAccessControl,
        private readonly LoadHistory $loadHistory,
        private readonly PrepareAccountTask $prepareAccountTask,
        private readonly CallNewTaskInterface $callNewTask,
        private readonly AddTaskToHistory $addTaskToHistory,
        private readonly PrepareRedirection $prepareRedirection,
        private readonly JumpIf $jumpIf,
        private readonly SetRedirectClientAtEnd $redirectClient,
        private readonly Render $render,
        private readonly RenderError $renderError,
        private readonly string|Stringable $defaultErrorTemplate,
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient(LoaderInterface::class, 'loader'));
        $recipe = $recipe->require(new Ingredient('string', 'id'));
        $recipe = $recipe->require(new Ingredient('string', 'taskClass'));
        $recipe = $recipe->require(
            new IngredientWithCondition(
                conditionCallback: fn (array &$workplan): bool => empty($workplan['api']),
                requiredType: 'string',
                name: 'route',
            )
        );
        $recipe = $recipe->require(
            new IngredientWithCondition(
                conditionCallback: fn (array &$workplan): bool => !empty($workplan['api']),
                requiredType: 'string',
                name: 'template',
            )
        );

        $recipe = $recipe->cook($this->loadObject, LoadObject::class, [], 10);

        $recipe = $recipe->cook($this->objectAccessControl, ObjectAccessControlInterface::class, [], 20);

        $recipe = $recipe->cook($this->loadHistory, LoadHistory::class, [], 25);

        $recipe = $recipe->cook($this->prepareAccountTask, PrepareAccountTask::class, [], 30);

        $recipe = $recipe->cook($this->callNewTask, CallNewTaskInterface::class, [], 40);

        $recipe = $recipe->cook($this->addTaskToHistory, AddTaskToHistory::class, [], 45);

        $recipe = $recipe->cook($this->prepareRedirection, PrepareRedirection::class, [], 50);

        $recipe = $recipe->cook(
            $this->jumpIf,
            JumpIf::class,
            [
                'testValue' => 'api',
                'nextStep' => new Value(Render::class),
            ],
            59,
        );

        $recipe = $recipe->cook($this->redirectClient, SetRedirectClientAtEnd::class, [], 60);

        $recipe = $recipe->cook(new Stop(), Stop::class, [], 65);

        $recipe = $recipe->cook($this->render, Render::class, [], 70);

        $recipe = $recipe->onError(new Bowl($this->renderError, []));

        $this->addToWorkplan('errorTemplate', (string) $this->defaultErrorTemplate);

        return $recipe;
    }
}
