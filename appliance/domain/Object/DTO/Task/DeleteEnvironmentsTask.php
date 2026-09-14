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

namespace Teknoo\Space\Object\DTO\Task;

/**
 * Task asking the worker to tear down, on their clusters, the environments an account has just dropped from
 * its edition form. The `AccountEnvironment` documents are already removed by the web request
 * (`DeleteEnvFromResumes`), so the worker cannot reload them: the task carries, for each removed
 * environment, its name, its cluster name and its namespace. One task is queued per edition, whatever the
 * number of removed environments.
 *
 * The list is exported to the workplan under `environmentsToDelete`, never under `namespace`, which the
 * account plans reserve for the account namespace.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
final class DeleteEnvironmentsTask extends AbstractAccountTask
{
    /**
     * @param array<object> $variables
     * @param list<array{envName: string, clusterName: string, namespace: string}> $environments
     */
    public function __construct(
        string $taskId = '',
        ?string $accountId = null,
        ?string $envName = null,
        ?string $clusterName = null,
        array $variables = [],
        public array $environments = [],
    ) {
        parent::__construct($taskId, $accountId, $envName, $clusterName, $variables);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return parent::toArray() + ['environmentsToDelete' => $this->environments];
    }
}
