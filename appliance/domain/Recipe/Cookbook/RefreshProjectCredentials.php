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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Recipe\Cookbook;

use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\East\Foundation\Recipe\CookbookInterface;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Cookbook\BaseCookbookTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Recipe\Step\AccountCredential\LoadCredentials;
use Teknoo\Space\Recipe\Step\Project\LoadAccountFromProject;
use Teknoo\Space\Recipe\Step\Project\UpdateProjectCredentialsFromAccount;
use Teknoo\Space\Recipe\Step\SpaceProject\PrepareRedirection as SpaceProjectPrepareRedirection;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class RefreshProjectCredentials implements CookbookInterface
{
    use BaseCookbookTrait;

    public function __construct(
        RecipeInterface $recipe,
        private LoadObject $loadObject,
        private ObjectAccessControlInterface $objectAccessControl,
        private LoadAccountFromProject $loadAccountFromProject,
        private LoadCredentials $loadCredentials,
        private UpdateProjectCredentialsFromAccount $updateProjectCredentialsFromAccount,
        private SaveObject $saveObject,
        private SpaceProjectPrepareRedirection $spaceProjectPrepareRedirection,
        private RedirectClientInterface $redirectClient,
        private RenderError $renderError,
        private string $defaultErrorTemplate,
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

        $recipe = $recipe->cook($this->loadCredentials, LoadCredentials::class, [], 30);

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

        $this->addToWorkplan('errorTemplate', $this->defaultErrorTemplate);

        return $recipe;
    }
}
