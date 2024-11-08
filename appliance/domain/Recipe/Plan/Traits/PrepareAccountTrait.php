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

use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Recipe\Step\JumpIf;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\Stop;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Recipe\Value;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Client\SetRedirectClientAtEnd;
use Teknoo\Space\Recipe\Step\AccountEnvironment\LoadEnvironments;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;
use Teknoo\Space\Recipe\Step\Account\PrepareRedirection;
use Teknoo\Space\Recipe\Step\AccountRegistry\LoadRegistryCredential;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait PrepareAccountTrait
{
    private function prepareRecipeForAccount(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->cook($this->loadObject, LoadObject::class, [], 10);

        $recipe = $recipe->cook($this->objectAccessControl, ObjectAccessControlInterface::class, [], 20);

        if (isset($this->jumpIf)) {
            $recipe = $recipe->cook(
                $this->jumpIf,
                JumpIf::class,
                [
                    'testValue' => 'api',
                    'nextStep' => new Value(LoadHistory::class),
                ],
                21,
            );
        }

        $recipe = $recipe->cook($this->prepareRedirection, PrepareRedirection::class, [], 30);

        $recipe = $recipe->cook($this->redirectClient, SetRedirectClientAtEnd::class, [], 40);

        $recipe = $recipe->cook($this->loadHistory, LoadHistory::class, [], 50);

        if (isset($this->loadCredentials)) {
            $recipe = $recipe->cook($this->loadCredentials, LoadEnvironments::class, [], 60);
        }

        if (isset($this->loadRegistryCredential)) {
            $recipe = $recipe->cook($this->loadRegistryCredential, LoadRegistryCredential::class, [], 60);
        }

        if (!isset($this->jumpIf) || !isset($this->render)) {
            return $recipe;
        }

        $recipe = $recipe->cook(
            $this->jumpIf,
            JumpIf::class,
            [
                'testValue' => 'api',
                'nextStep' => new Value(Render::class),
            ],
            130,
        );

        $recipe = $recipe->cook(
            new Stop(),
            Stop::class,
            [],
            135,
        );

        $recipe = $recipe->cook($this->render, Render::class, [], 140);

        return $recipe;
    }
}
