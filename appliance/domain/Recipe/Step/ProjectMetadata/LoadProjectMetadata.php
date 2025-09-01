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

namespace Teknoo\Space\Recipe\Step\ProjectMetadata;

use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Loader\ProjectMetadataLoader;
use Teknoo\Space\Object\Persisted\ProjectMetadata;
use Teknoo\Space\Query\ProjectMetadata\LoadFromProjectQuery;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LoadProjectMetadata
{
    public function __construct(
        private readonly ProjectMetadataLoader $loader,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        Project $project,
        ParametersBag $bag,
    ): self {
        /** @var Promise<ProjectMetadata, mixed, mixed> $fetchedPromise */
        $fetchedPromise = new Promise(
            static function (ProjectMetadata $metadata) use ($manager, $bag): void {
                $bag->set('projectMetadata', $metadata);

                $manager->updateWorkPlan([
                    ProjectMetadata::class => $metadata,
                ]);
            }
        );

        $this->loader->fetch(
            new LoadFromProjectQuery($project),
            $fetchedPromise
        );

        return $this;
    }
}
