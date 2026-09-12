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

namespace Teknoo\Space\Recipe\Step\Task;

use DomainException;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Contracts\DTO\NewTaskInterface;
use Teknoo\Space\Object\DTO\Task\AbstractAccountTask;

use function is_a;

/**
 * Build the account provisioning task to hand to `CallNewTask`, from the loaded account and, for
 * environment tasks, the `envName` / `clusterName` already in the workplan (route parameters, or
 * `PrepareInstall` in the account edit loop). The task class comes from the route defaults or from a
 * `Step` mapping in the DI configuration.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class PrepareAccountTask
{
    public function __invoke(
        ManagerInterface $manager,
        Account $accountInstance,
        string $taskClass,
        ?string $envName = null,
        ?string $clusterName = null,
    ): self {
        if (!is_a($taskClass, AbstractAccountTask::class, true)) {
            throw new DomainException('teknoo.space.error.task.invalid_class', 500);
        }

        $manager->updateWorkPlan([
            NewTaskInterface::class => new $taskClass(
                accountId: (string) $accountInstance->getId(),
                envName: $envName,
                clusterName: $clusterName,
            ),
        ]);

        return $this;
    }
}
