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

namespace Teknoo\Space\Recipe\Cookbook\Traits;

use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Client\SetRedirectClientAtEnd;
use Teknoo\Space\Recipe\Step\AccountCredential\LoadCredentials;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;
use Teknoo\Space\Recipe\Step\Account\PrepareRedirection;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait PrepareAccountTrait
{
    private function prepareRecipeForAccount(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->cook($this->loadObject, LoadObject::class, [], 10);

        $recipe = $recipe->cook($this->objectAccessControl, ObjectAccessControlInterface::class, [], 20);

        $recipe = $recipe->cook($this->prepareRedirection, PrepareRedirection::class, [], 30);

        $recipe = $recipe->cook($this->redirectClient, SetRedirectClientAtEnd::class, [], 40);

        $recipe = $recipe->cook($this->loadHistory, LoadHistory::class, [], 50);

        return $recipe->cook($this->loadCredentials, LoadCredentials::class, [], 60);
    }
}
