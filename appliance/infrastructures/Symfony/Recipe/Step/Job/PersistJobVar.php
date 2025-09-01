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

namespace Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Job;

use Teknoo\East\Common\Contracts\DBSource\ManagerInterface as DbSourceManager;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Object\DTO\NewJob;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\Persisted\ProjectPersistedVariable;
use Teknoo\Space\Writer\ProjectPersistedVariableWriter;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class PersistJobVar
{
    public function __construct(
        private readonly ProjectPersistedVariableWriter $writer,
        private readonly DbSourceManager $manager,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        NewJob $newJob,
        SpaceProject $spaceProject,
    ): PersistJobVar {
        $this->manager->openBatch();
        $project = $spaceProject->project;
        $final = [];

        /** @var Promise<ProjectPersistedVariable, mixed, mixed> */
        $promise = new Promise(
            onSuccess: static function (ProjectPersistedVariable $var) use (&$final): void {
                $final[] = $var; //To avoid collision with \spl_object_hash
            },
            onFail: $manager->error(...),
        );

        $promise->allowReuse();

        foreach ($newJob->variables as $variable) {
            if ($variable->persisted && $variable->canPersist) {
                $nE = !empty($variable->value) && $variable->secret && empty($variable->encryptionAlgorithm);

                $persistedVariable = new ProjectPersistedVariable(
                    project: $project,
                    id: $variable->getId(),
                    name: $variable->name,
                    value: $variable->value,
                    envName: $newJob->envName ?? 'default',
                    secret: $variable->secret,
                    encryptionAlgorithm: $variable->encryptionAlgorithm,
                    needEncryption: $nE,
                );

                $this->writer->save(
                    $persistedVariable,
                    $promise,
                );
            }
        }

        $this->manager->closeBatch();

        return $this;
    }
}
