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

namespace Teknoo\Space\Recipe\Plan\Traits;

use Psr\Http\Message\ServerRequestInterface;
use Stringable;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait EditOwnsSettingsTrait
{
    public function __construct(
        RecipeInterface $recipe,
        private readonly FormHandlingInterface $formHandling,
        private readonly FormProcessingInterface $formProcessing,
        private readonly SaveObject $saveObject,
        private readonly RenderFormInterface $renderForm,
        private readonly RenderError $renderError,
        private readonly string $objectClass,
        private readonly string|Stringable $defaultErrorTemplate,
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient(requiredType: ServerRequestInterface::class, name: 'request'));
        $recipe = $recipe->require(new Ingredient(requiredType: LoaderInterface::class, name: 'loader'));
        $recipe = $recipe->require(new Ingredient(requiredType: WriterInterface::class, name: 'writer'));
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

        $mapping = [
            'object' => $this->objectClass,
        ];

        $recipe = $recipe->cook($this->formHandling, FormHandlingInterface::class, $mapping, 30);

        $recipe = $recipe->cook($this->formProcessing, FormProcessingInterface::class, [], 40);

        $recipe = $recipe->cook($this->saveObject, SaveObject::class, $mapping, 60);

        $recipe = $recipe->cook($this->formHandling, FormHandlingInterface::class . ':refresh', $mapping, 69);

        $recipe = $recipe->cook($this->renderForm, RenderFormInterface::class, $mapping, 70);

        $recipe = $recipe->onError(new Bowl($this->renderError, []));

        $this->addToWorkplan('errorTemplate', (string) $this->defaultErrorTemplate);

        $this->addToWorkplan('nextStep', RenderFormInterface::class);

        return $recipe;
    }
}
