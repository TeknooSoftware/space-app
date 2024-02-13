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

namespace Teknoo\Space\Recipe\Cookbook;

use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Foundation\Recipe\CookbookInterface;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Cookbook\BaseCookbookTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Recipe\Step\Job\PrepareNewJobForm;
use Teknoo\Space\Recipe\Step\PersistedVariable\LoadPersistedVariablesForJob;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class JobRestart implements CookbookInterface
{
    use BaseCookbookTrait;

    public function __construct(
        RecipeInterface $recipe,
        private readonly LoadObject $loadObject,
        private readonly ObjectAccessControlInterface $objectAccessControl,
        private readonly CreateObject $createObject,
        private readonly LoadPersistedVariablesForJob $loadPersistedVariablesForJob,
        private readonly PrepareNewJobForm $prepareNewJobForm,
        private readonly FormHandlingInterface $formHandling,
        private readonly RenderFormInterface $renderForm,
        private readonly RenderError $renderError,
        private readonly string $defaultErrorTemplate,
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient(requiredType: 'string', name: 'route'));
        $recipe = $recipe->require(new Ingredient(requiredType: 'string', name: 'objectClass'));
        $recipe = $recipe->require(new Ingredient(requiredType: 'string', name: 'formClass'));
        $recipe = $recipe->require(
            new Ingredient(
                requiredType: 'array',
                name: 'formOptions',
                mandatory: false,
                default: [],
            )
        );
        $recipe = $recipe->require(new Ingredient(requiredType: 'string', name: 'template'));
        $recipe = $recipe->require(new Ingredient(requiredType: 'string', name: 'projectId'));
        $recipe = $recipe->require(new Ingredient(requiredType: 'string', name: 'jobId'));

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

        $recipe = $recipe->cook($this->createObject, CreateObject::class, [], 10);

        $recipe = $recipe->cook($this->loadPersistedVariablesForJob, LoadPersistedVariablesForJob::class, [], 20);

        $recipe = $recipe->cook($this->prepareNewJobForm, PrepareNewJobForm::class, [], 30);

        $recipe = $recipe->cook($this->formHandling, FormHandlingInterface::class, [], 40);

        $recipe = $recipe->cook($this->renderForm, RenderFormInterface::class, [], 60);

        $recipe = $recipe->onError(new Bowl($this->renderError, []));

        $this->addToWorkplan('nextStep', RenderFormInterface::class);

        $this->addToWorkplan('errorTemplate', $this->defaultErrorTemplate);

        return $recipe;
    }
}
