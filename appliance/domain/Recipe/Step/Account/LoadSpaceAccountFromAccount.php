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
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Loader\Meta\SpaceAccountLoader;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Throwable;

use function in_array;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LoadSpaceAccountFromAccount
{
    public function __construct(
        private readonly SpaceAccountLoader $spaceAccountLoader,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        ?Account $accountInstance = null,
        ?SpaceAccount $spaceAccount = null,
        ?User $user = null,
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

        if (!in_array('ROLE_ADMIN', (array) $user?->getRoles())) {
            $manager->error(
                new BadMethodCallException(message: "Account is mandatory for non admin user", code: 403)
            );

            return $this;
        }

        //Request come from admin
        /** @var Promise<SpaceAccount, mixed, mixed> $promiseAccount */
        $promiseAccount = new Promise(
            static function (SpaceAccount $spaceAccount) use ($manager): void {
                $manager->updateWorkPlan([
                    SpaceAccount::class => $spaceAccount,
                ]);
            },
            static fn (Throwable $error): never => throw $error,
        );

        $this->spaceAccountLoader->load(
            $accountInstance->getId(),
            $promiseAccount,
        );

        return $this;
    }
}
