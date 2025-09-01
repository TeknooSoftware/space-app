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
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Recipe\Step\AccountCluster\LoadAccountClusters;
use Teknoo\Space\Recipe\Step\AccountEnvironment\LoadEnvironments;
use Teknoo\Space\Recipe\Step\AccountRegistry\LoadRegistryCredential;
use Teknoo\Space\Recipe\Step\Project\LoadAccountFromProject;
use Teknoo\Space\Recipe\Step\Project\UpdateProjectCredentialsFromAccount;
use Teknoo\Space\Recipe\Step\SpaceProject\PrepareRedirection as SpaceProjectPrepareRedirection;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class RefreshProjectCredentials implements EditablePlanInterface
{
    use EditablePlanTrait;

    public function __construct(
        RecipeInterface $recipe,
        private readonly LoadObject $loadObject,
        private readonly ObjectAccessControlInterface $objectAccessControl,
        private readonly LoadAccountFromProject $loadAccountFromProject,
        private readonly LoadEnvironments $loadEnvironments,
        private readonly LoadAccountClusters $loadAccountClusters,
        private readonly LoadRegistryCredential $loadRegistryCredential,
        private readonly UpdateProjectCredentialsFromAccount $updateProjectCredentialsFromAccount,
        private readonly SaveObject $saveObject,
        private readonly SpaceProjectPrepareRedirection $spaceProjectPrepareRedirection,
        private readonly RedirectClientInterface $redirectClient,
        private readonly RenderError $renderError,
        private readonly string|Stringable $defaultErrorTemplate,
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient('string', 'route'));
        $recipe = $recipe->require(new Ingredient('string', 'id'));

        $recipe = $recipe->cook(
            $this->loadObject,
            LoadObject::class,
            [
                'workPlanKey' => 'projectKey'
            ],
            05,
        );

        $recipe = $recipe->cook(
            $this->objectAccessControl,
            ObjectAccessControlInterface::class,
            [
                'object' => Project::class,
            ],
            06,
        );

        $recipe = $recipe->cook($this->loadAccountFromProject, LoadAccountFromProject::class, [], 20);

        $recipe = $recipe->cook($this->loadEnvironments, LoadEnvironments::class, [], 30);

        $recipe = $recipe->cook($this->loadAccountClusters, LoadAccountClusters::class, [], 30);

        $recipe = $recipe->cook($this->loadRegistryCredential, LoadRegistryCredential::class, [], 30);

        $recipe = $recipe->cook(
            $this->updateProjectCredentialsFromAccount,
            UpdateProjectCredentialsFromAccount::class,
            [],
            40,
        );

        $recipe = $recipe->cook(
            $this->saveObject,
            SaveObject::class,
            [
                'object' => Project::class,
            ],
            50,
        );

        $recipe = $recipe->cook($this->spaceProjectPrepareRedirection, SpaceProjectPrepareRedirection::class, [], 60);

        $recipe = $recipe->cook(
            $this->redirectClient,
            RedirectClientInterface::class,
            [

            ],
            70,
        );

        $recipe = $recipe->onError(new Bowl($this->renderError, []));

        $this->addToWorkplan('nextStep', RenderFormInterface::class);

        $this->addToWorkplan('errorTemplate', (string) $this->defaultErrorTemplate);

        return $recipe;
    }
}
