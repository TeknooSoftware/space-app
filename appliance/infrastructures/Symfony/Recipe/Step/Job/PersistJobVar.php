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

namespace Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Job;

use Teknoo\East\Paas\Object\Job;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Object\DTO\NewJob;
use Teknoo\Space\Object\Persisted\PersistedVariable;
use Teknoo\Space\Writer\PersistedVariableWriter;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class PersistJobVar
{
    public function __construct(
        private PersistedVariableWriter $writer,
    ) {
    }

    public function __invoke(
        NewJob $newJob,
        Project $project,
        Job $job,
    ): PersistJobVar {
        foreach ($newJob->variables as $variable) {
            if ($variable->persisted) {
                $persistedVariable = new PersistedVariable(
                    project: $project,
                    id: $variable->getId(),
                    name: $variable->name,
                    value: $variable->value,
                    environmentName: $newJob->envName ?? 'default',
                    secret: $variable->secret,
                );

                $this->writer->save($persistedVariable);
            }
        }

        return $this;
    }
}
