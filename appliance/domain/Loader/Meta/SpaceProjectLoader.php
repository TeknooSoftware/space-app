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
 * @link        https://teknoo.software/applications/space Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Loader\Meta;

use DomainException;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Query\QueryCollectionInterface;
use Teknoo\East\Common\Contracts\Query\QueryElementInterface;
use Teknoo\East\Common\Object\Collection\LazyLoadableCollection;
use Teknoo\East\Paas\Loader\ProjectLoader;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Loader\ProjectPersistedVariableLoader;
use Teknoo\Space\Loader\ProjectMetadataLoader;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\Persisted\ProjectMetadata;
use Teknoo\Space\Query\ProjectPersistedVariable\LoadFromProjectQuery as LoadVariablesFromProjectQuery;
use Teknoo\Space\Query\ProjectMetadata\LoadFromProjectQuery as LoadMetaDataFromProjectQuery;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements LoaderInterface<SpaceProject>
 */
class SpaceProjectLoader implements LoaderInterface
{
    public function __construct(
        private ProjectLoader $projectLoader,
        private ProjectMetadataLoader $metadataLoader,
        private ProjectPersistedVariableLoader $persistedVariableLoader,
    ) {
    }

    /**
     * @param Project $project
     * @param PromiseInterface<SpaceProject, mixed> $promise
     */
    private function fetchMetadata(Project $project, PromiseInterface $promise): self
    {
        $persistedVariableLoader = $this->persistedVariableLoader;

        /** @var Promise<ProjectMetadata, mixed, SpaceProject> $fetchedPromise */
        $fetchedPromise = new Promise(
            static function (ProjectMetadata $data, PromiseInterface $next) use ($project, $persistedVariableLoader) {
                $next->success(
                    new SpaceProject(
                        $project,
                        $data,
                        new LazyLoadableCollection(
                            $persistedVariableLoader,
                            new LoadVariablesFromProjectQuery($project),
                        )
                    )
                );
            },
            static fn (Throwable $error, PromiseInterface $next) => $next->fail(
                new DomainException(
                    message: 'teknoo.space.error.space_project.project_metadata.fetching',
                    code: $error->getCode() > 0 ? $error->getCode() : 404,
                    previous: $error,
                )
            ),
            true
        );

        $this->metadataLoader->fetch(
            new LoadMetaDataFromProjectQuery($project),
            $fetchedPromise->next($promise)
        );

        return $this;
    }

    public function load(string $id, PromiseInterface $promise): LoaderInterface
    {
        /** @var Promise<Project, mixed, SpaceProject> $fetchedPromise */
        $fetchedPromise = new Promise(
            fn (Project $project, PromiseInterface $next) => $this->fetchMetadata($project, $next),
            static fn (Throwable $error, PromiseInterface $next) => $next->fail(
                new DomainException(
                    message: 'teknoo.space.error.space_project.project.fetching',
                    code: $error->getCode() > 0 ? $error->getCode() : 404,
                    previous: $error,
                )
            ),
            true
        );

        $this->projectLoader->load(
            $id,
            $fetchedPromise->next($promise)
        );
        return $this;
    }

    public function query(QueryCollectionInterface $query, PromiseInterface $promise): LoaderInterface
    {
        /** @var Promise<iterable<Project>, mixed, iterable<SpaceProject>> $fetchedPromise */
        $fetchedPromise = new Promise(
            static function (iterable $result) {
                $final = [];
                foreach ($result as $project) {
                    $final[] = new SpaceProject($project, null); //Not needed actually to fetch metdata
                }

                return $final;
            },
            allowNext: true,
        );

        $this->projectLoader->query(
            $query,
            $fetchedPromise->next($promise, true)
        );

        return $this;
    }

    public function fetch(QueryElementInterface $query, PromiseInterface $promise): LoaderInterface
    {
        /** @var Promise<Project, mixed, SpaceProject> $fetchedPromise */
        $fetchedPromise = new Promise(
            function (Project $project, PromiseInterface $next) {
                $this->fetchMetadata($project, $next);
            },
            static fn (Throwable $error, PromiseInterface $next) => $next->fail(
                new DomainException(
                    message: 'teknoo.space.error.space_project.project_metadata.fetching',
                    code: $error->getCode() > 0 ? $error->getCode() : 404,
                    previous: $error,
                )
            ),
            true
        );

        $this->projectLoader->fetch(
            $query,
            $fetchedPromise->next($promise)
        );

        return $this;
    }
}
