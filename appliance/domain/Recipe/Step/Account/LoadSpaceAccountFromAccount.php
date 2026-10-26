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

namespace Teknoo\Space\Recipe\Step\Account;

use BadMethodCallException;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\DTO\SpaceAccount;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LoadSpaceAccountFromAccount
{
    public function __invoke(
        ManagerInterface $manager,
        ?Account $accountInstance = null,
        ?SpaceAccount $spaceAccount = null,
    ): self {
        if (
            $spaceAccount instanceof SpaceAccount
            && $accountInstance instanceof Account
            && $spaceAccount->account->getId() === $accountInstance->getId()
        ) {
            //Account used to create project is logged used
            return $this;
        }

        if (null === $accountInstance) {
            $manager->error(
                new BadMethodCallException(message: "An account is mandatory to create a project", code: 404)
            );

            return $this;
        }

        //A project is created only on the account of the user: an administrator of Space has no account
        $manager->error(
            new BadMethodCallException(message: "The account is not the account of the user", code: 403)
        );

        return $this;
    }
}
