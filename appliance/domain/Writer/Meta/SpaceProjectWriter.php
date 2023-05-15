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

namespace Teknoo\Space\Writer\Meta;

use RuntimeException;
use Teknoo\East\Common\Contracts\DBSource\BatchManipulationManagerInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Paas\Object\Project;
use Teknoo\East\Paas\Writer\ProjectWriter;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\Persisted\PersistedVariable;
use Teknoo\Space\Object\Persisted\ProjectMetadata;
use Teknoo\Space\Query\PersistedVariable\DeleteVariablesQuery;
use Teknoo\Space\Writer\PersistedVariableWriter;
use Teknoo\Space\Writer\ProjectMetadataWriter;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements WriterInterface<SpaceProject>
 */
class SpaceProjectWriter implements WriterInterface
{
    public function __construct(
        private ProjectWriter $projectWriter,
        private ProjectMetadataWriter $metadataWriter,
        private PersistedVariableWriter $persistedVariableWriter,
        private BatchManipulationManagerInterface $batchManipulationManager,
    ) {
    }

    public function save(
        ObjectInterface $object,
        PromiseInterface $promise = null,
        ?bool $prefereRealDateOnUpdate = null,
    ): WriterInterface {
        if (!$object instanceof SpaceProject) {
            $promise?->fail(new RuntimeException($object::class . 'is not supported by this writer', 500));

            return $this;
        }

        if (!$object->project instanceof Project) {
            $promise?->fail(new RuntimeException(
                message: 'teknoo.space.error.space_project.writer.not_instantiable',
                code :500
            ));

            return $this;
        }

        /** @var Promise<Project, mixed, mixed> $persistedPromise */
        $persistedPromise = new Promise(
            function (Project $project, PromiseInterface $next) use ($object, $prefereRealDateOnUpdate) {
                if ($object->projectMetadata instanceof ProjectMetadata) {
                    $metadata = $object->projectMetadata;
                    $metadata->setProject($object->project);

                    $this->metadataWriter->save($metadata, $next, $prefereRealDateOnUpdate);
                }

                $ids = [];
                foreach ($object->variables as $var) {
                    $this->persistedVariableWriter->save(
                        object: $var,
                        prefereRealDateOnUpdate: $prefereRealDateOnUpdate
                    );
                    $ids[] = $var->getId();
                }

                /** @var Promise<PersistedVariable, mixed, mixed> $deletedPromise */
                $deletedPromise = new Promise(
                    null,
                    fn (Throwable $error) => throw $error,
                );
                $this->batchManipulationManager->deleteQuery(
                    new DeleteVariablesQuery($object->project, $ids),
                    $deletedPromise,
                );
            },
            static fn (Throwable $error, ?PromiseInterface $next = null) => $next?->fail(
                new RuntimeException(
                    message: 'teknoo.space.error.space_project.project.persisting',
                    code: $error->getCode() > 0 ? $error->getCode() : 500,
                    previous: $error,
                )
            ),
            true,
        );

        $this->projectWriter->save(
            $object->project,
            $persistedPromise->next($promise),
            $prefereRealDateOnUpdate
        );

        return $this;
    }

    public function remove(ObjectInterface $object, PromiseInterface $promise = null): WriterInterface
    {
        if (!$object instanceof SpaceProject) {
            $promise?->fail(new RuntimeException($object::class . 'is not supported by this writer', 500));

            return $this;
        }

        if ($object->projectMetadata instanceof ProjectMetadata) {
            /** @var Promise<ProjectMetadata, mixed, mixed> $removedPromise */
            $removedPromise = new Promise(
                function (mixed $result, ?PromiseInterface $next = null) use ($object) {
                    if ($object->project instanceof Project) {
                        $this->projectWriter->remove($object->project, $next);
                    }

                    foreach ($object->variables as $var) {
                        $this->persistedVariableWriter->remove($var);
                    }
                },
                static fn (Throwable $error, ?PromiseInterface $next = null) => $next?->fail(
                    new RuntimeException(
                        message: 'teknoo.space.error.space_project.project.deleting',
                        code: $error->getCode() > 0 ? $error->getCode() : 500,
                        previous: $error,
                    )
                ),
                true,
            );

            $this->metadataWriter->remove(
                $object->projectMetadata,
                $removedPromise->next($promise)
            );
        }

        return $this;
    }
}
