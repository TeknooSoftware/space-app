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

namespace Teknoo\Space\Recipe\Plan\Traits;

use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Recipe\Step\JumpIf;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\Stop;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Recipe\Value;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Client\SetRedirectClientAtEnd;
use Teknoo\Space\Recipe\Step\AccountCluster\LoadAccountClusters;
use Teknoo\Space\Recipe\Step\AccountEnvironment\LoadEnvironments;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;
use Teknoo\Space\Recipe\Step\Account\PrepareRedirection;
use Teknoo\Space\Recipe\Step\AccountRegistry\LoadRegistryCredential;

use function is_callable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
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

        if (isset($this->loadEnvironments) && $this->loadEnvironments instanceof LoadEnvironments) {
            $recipe = $recipe->cook($this->loadEnvironments, LoadEnvironments::class, [], 60);
        }

        if (isset($this->loadRegistryCredential) && $this->loadRegistryCredential instanceof LoadRegistryCredential) {
            $recipe = $recipe->cook($this->loadRegistryCredential, LoadRegistryCredential::class, [], 60);
        }

        if (isset($this->loadAccountClusters) && $this->loadAccountClusters instanceof LoadAccountClusters) {
            $recipe = $recipe->cook($this->loadAccountClusters, LoadAccountClusters::class, [], 60);
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
