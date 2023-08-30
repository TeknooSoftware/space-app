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

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Foundation\Recipe\CookbookInterface;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Cookbook\BaseCookbookTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Recipe\Step\Job\ExtractProject;
use Teknoo\Space\Recipe\Step\ProjectMetadata\InjectToViewMetadata;
use Teknoo\Space\Recipe\Step\ProjectMetadata\LoadProjectMetadata;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class JobGet implements CookbookInterface
{
    use BaseCookbookTrait;

    public function __construct(
        RecipeInterface $recipe,
        private readonly LoadObject $loadObject,
        private readonly ExtractProject $extractProject,
        private readonly LoadProjectMetadata $loadProjectMetadata,
        private readonly InjectToViewMetadata $injectToViewMetadata,
        private readonly Render $render,
        private readonly RenderError $renderError,
        private readonly ObjectAccessControlInterface $objectAccessControl,
        private readonly string $defaultErrorTemplate,
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient(ServerRequestInterface::class, 'request'));
        $recipe = $recipe->require(new Ingredient(LoaderInterface::class, 'loader'));
        $recipe = $recipe->require(new Ingredient('string', 'id'));
        $recipe = $recipe->require(new Ingredient('string', 'template'));

        $recipe = $recipe->cook($this->loadObject, LoadObject::class, [], 10);

        $recipe = $recipe->cook($this->extractProject, ExtractProject::class, [], 20);

        $recipe = $recipe->cook($this->objectAccessControl, ObjectAccessControlInterface::class, [], 30);

        $recipe = $recipe->cook($this->loadProjectMetadata, LoadProjectMetadata::class, [], 40);

        $recipe = $recipe->cook($this->injectToViewMetadata, InjectToViewMetadata::class, [], 50);

        $recipe = $recipe->cook(
            $this->render,
            Render::class,
            [
                'objectInstance' => ObjectInterface::class,
            ],
            60
        );

        $recipe = $recipe->onError(new Bowl($this->renderError, []));

        $this->addToWorkplan('errorTemplate', $this->defaultErrorTemplate);

        return $recipe;
    }
}
