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
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\JumpIf;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\Ingredient\IngredientWithCondition;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Contracts\Recipe\Step\Job\CallNewJobInterface;
use Teknoo\Space\Contracts\Recipe\Step\Job\NewJobNotifierInterface;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Job\PersistJobVar;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Recipe\Step\AccountCluster\LoadAccountClusters;
use Teknoo\Space\Recipe\Step\Job\PrepareNewJobForm;
use Teknoo\Space\Recipe\Step\NewJob\NewJobSetDefaults;
use Teknoo\Space\Recipe\Step\PersistedVariable\LoadPersistedVariablesForJob;
use Teknoo\Space\Recipe\Step\Project\LoadAccountFromProject;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class JobStart implements EditablePlanInterface
{
    use EditablePlanTrait;

    public function __construct(
        RecipeInterface $recipe,
        private readonly LoadObject $loadObject,
        private readonly ObjectAccessControlInterface $objectAccessControl,
        private readonly LoadAccountFromProject $loadAccountFromProject,
        private readonly CreateObject $createObject,
        private readonly PrepareNewJobForm $prepareNewJobForm,
        private readonly LoadPersistedVariablesForJob $loadPersistedVariablesForJob,
        private readonly LoadAccountClusters $loadAccountClusters,
        private readonly FormHandlingInterface $formHandling,
        private readonly FormProcessingInterface $formProcessing,
        private readonly NewJobSetDefaults $newJobSetDefaults,
        private readonly PersistJobVar $persistJobVar,
        private readonly NewJobNotifierInterface $newJobNotifier,
        private readonly JumpIf $jumpIf,
        private readonly CallNewJobInterface $callNewJob,
        private readonly RedirectClientInterface $redirectClient,
        private readonly RenderFormInterface $renderForm,
        private readonly RenderError $renderError,
        private readonly string|Stringable $defaultErrorTemplate,
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {

        $recipe = $recipe->require(
            new IngredientWithCondition(
                conditionCallback: fn (array &$workplan) => empty($workplan['api']),
                requiredType: 'string',
                name: 'route'
            )
        );

        $recipe = $recipe->require(new Ingredient(ClusterCatalog::class, 'clusterCatalog'));
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
        $recipe = $recipe->cook($this->loadAccountFromProject, LoadAccountFromProject::class, [], 07);

        $recipe = $recipe->cook($this->createObject, CreateObject::class, [], 10);

        $recipe = $recipe->cook($this->loadPersistedVariablesForJob, LoadPersistedVariablesForJob::class, [], 20);

        $recipe = $recipe->cook($this->loadAccountClusters, LoadAccountClusters::class, [], 20);

        $recipe = $recipe->cook($this->prepareNewJobForm, PrepareNewJobForm::class, [], 30);

        $recipe = $recipe->cook($this->formHandling, FormHandlingInterface::class, [], 40);

        $recipe = $recipe->cook($this->formProcessing, FormProcessingInterface::class, [], 50);

        $recipe = $recipe->cook($this->newJobSetDefaults, NewJobSetDefaults::class, [], 60);

        $recipe = $recipe->cook($this->persistJobVar, PersistJobVar::class, [], 65);

        $recipe = $recipe->cook($this->newJobNotifier, NewJobNotifierInterface::class, [], 70);

        $recipe = $recipe->cook($this->callNewJob, CallNewJobInterface::class, [], 80);

        $recipe = $recipe->cook(
            $this->jumpIf,
            JumpIf::class,
            [
                'testValue' => 'api',
            ],
            89,
        );

        $recipe = $recipe->cook(
            $this->redirectClient,
            RedirectClientInterface::class,
            [
                'parameters' => 'routeParameters'
            ],
            90
        );

        $recipe = $recipe->cook($this->renderForm, RenderFormInterface::class, [], 100);

        $recipe = $recipe->onError(new Bowl($this->renderError, []));

        $this->addToWorkplan('nextStep', RenderFormInterface::class);

        $this->addToWorkplan('errorTemplate', (string) $this->defaultErrorTemplate);

        return $recipe;
    }
}
