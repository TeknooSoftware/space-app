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

namespace Teknoo\Space\Recipe\Step\Project;

use DomainException;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Object\DTO\SpaceProject;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LoadAccountFromProject
{
    /**
     * @param array<string, string> $parameters
     */
    public function __invoke(
        SpaceProject|Project $spaceProject,
        ManagerInterface $manager,
        ?Account $account = null,
        ?string $accountId = null,
        array $parameters = [],
    ): LoadAccountFromProject {
        if ($spaceProject instanceof SpaceProject) {
            $spaceProject = $spaceProject->project;
        }

        $account ??= $spaceProject->getAccount();

        if (null !== $accountId) {
            if ($accountId !== $account->getId()) {
                throw new DomainException(
                    message: 'teknoo.space.error.space_account.account.fetching',
                    code: 404,
                );
            }

            $parameters['accountId'] = $accountId;
        }

        $manager->updateWorkPlan([
            Account::class => $account,
            'parameters' => $parameters,
        ]);

        return $this;
    }
}
