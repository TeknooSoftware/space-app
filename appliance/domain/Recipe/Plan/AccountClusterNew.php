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
use Teknoo\East\Common\Recipe\Plan\CreateObjectEndPoint;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\JumpIfNot;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Recipe\Value;
use Teknoo\Space\Recipe\Step\Account\InjectToView;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountClusterNew extends CreateObjectEndPoint
{
    public function __construct(
        RecipeInterface $recipe,
        private readonly JumpIfNot $jumpIfNot,
        private readonly LoadObject $loadObject,
        private readonly ObjectAccessControlInterface $objectAccessControl,
        CreateObject $createObject,
        FormHandlingInterface $formHandling,
        FormProcessingInterface $formProcessing,
        SaveObject $saveObject,
        private readonly InjectToView $injectToView,
        RedirectClientInterface $redirectClient,
        RenderFormInterface $renderForm,
        RenderError $renderError,
        string|Stringable $defaultErrorTemplate,
    ) {
        parent::__construct(
            recipe: $recipe,
            createObject: $createObject,
            formHandling: $formHandling,
            formProcessing: $formProcessing,
            slugPreparation: null,
            saveObject: $saveObject,
            redirectClient: $redirectClient,
            renderForm: $renderForm,
            renderError: $renderError,
            objectAccessControl: $this->objectAccessControl,
            defaultErrorTemplate: $defaultErrorTemplate,
            createObjectWiths: [
                'constructorArguments' => Account::class,
            ],
        );
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = parent::populateRecipe($recipe);

        $recipe = $recipe->require(
            new Ingredient(
                requiredType: 'string',
                name: 'accountId',
                mandatory: false,
                default: '',
            )
        );
        $recipe = $recipe->require(
            new Ingredient(
                requiredType: 'bool',
                name: 'allowAccountSelection',
                mandatory: false,
                default: false,
            )
        );

        $recipe = $recipe->cook(
            $this->jumpIfNot,
            JumpIfNot::class,
            [
                'testValue' => 'allowAccountSelection',
                'nextStep' => new Value(CreateObject::class),
            ],
            04,
        );

        $recipe = $recipe->cook(
            $this->loadObject,
            LoadObject::class . ':Account',
            [
                'loader' => 'accountLoader',
                'id' => 'accountId',
                'workPlanKey' => new Value(Account::class),
            ],
            05,
        );

        $recipe = $recipe->cook(
            $this->objectAccessControl,
            ObjectAccessControlInterface::class . ':Account',
            [
                'object' => Account::class,
            ],
            06,
        );

        $recipe = $recipe->cook(
            $this->injectToView,
            InjectToView::class,
            [],
            07,
        );

        return $recipe;
    }
}
