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
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\Stop;
use Teknoo\East\Foundation\Recipe\CookbookInterface;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Cookbook\BaseCookbookTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Contracts\Recipe\Step\User\JwtCreateTokenInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class UserGetJwtToken implements CookbookInterface
{
    use BaseCookbookTrait;

    public function __construct(
        RecipeInterface $recipe,
        private readonly CreateObject $createObject,
        private readonly FormHandlingInterface $formHandling,
        private readonly FormProcessingInterface $formProcessing,
        private readonly JwtCreateTokenInterface $jwtCreateToken,
        private readonly Render $render,
        private readonly Stop $stop,
        private readonly RenderFormInterface $renderForm,
        private readonly RenderError $renderError,
        private readonly string $defaultErrorTemplate,
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient(ServerRequestInterface::class, 'request'));
        $recipe = $recipe->require(new Ingredient('string', 'objectClass'));
        $recipe = $recipe->require(new Ingredient('string', 'formClass'));
        $recipe = $recipe->require(new Ingredient('array', 'formOptions'));
        $recipe = $recipe->require(new Ingredient('string', 'template'));
        $recipe = $recipe->require(new Ingredient('string', 'tokenTemplate'));

        $recipe = $recipe->cook($this->createObject, CreateObject::class, [], 10);

        $recipe = $recipe->cook($this->formHandling, FormHandlingInterface::class, [], 20);

        $recipe = $recipe->cook($this->formProcessing, FormProcessingInterface::class, [], 30);

        $recipe = $recipe->cook($this->jwtCreateToken, JwtCreateTokenInterface::class, [], 40);

        $recipe = $recipe->cook(
            $this->render,
            Render::class,
            [
                'template' => 'tokenTemplate',
            ],
            50,
        );

        $recipe = $recipe->cook($this->stop, Stop::class, [], 60);

        $recipe = $recipe->cook($this->renderForm, RenderFormInterface::class, [], 70);

        $recipe = $recipe->onError(new Bowl($this->renderError, []));

        $this->addToWorkplan('nextStep', RenderFormInterface::class);

        $this->addToWorkplan('errorTemplate', $this->defaultErrorTemplate);

        return $recipe;
    }
}
