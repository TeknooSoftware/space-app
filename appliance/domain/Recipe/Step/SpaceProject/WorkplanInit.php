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

namespace Teknoo\Space\Recipe\Step\SpaceProject;

use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Environment;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\Persisted\ProjectMetadata;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class WorkplanInit
{
    public function __invoke(
        ManagerInterface $manager,
        SpaceProject $spaceProject,
        ?Project $project = null,
        ?ProjectMetadata $metadata = null,
        bool $populateFormOptions = false,
    ): self {
        $projectInstance = $spaceProject->project ?? $project;

        $workPlan = [
            Project::class => $projectInstance,
            ProjectMetadata::class => $spaceProject->projectMetadata ?? $metadata,
        ];

        if (null !== $projectInstance && $populateFormOptions) {
            $environmentsList = [];

            $projectInstance->listMeYourEnvironments(
                static function (Environment $env) use (&$environmentsList) {
                    $environmentsList[(string)$env] = (string)$env;
                }
            );

            $workPlan['formOptions'] = [
                'environmentsList' => $environmentsList,
            ];
        }

        $manager->updateWorkPlan($workPlan);

        return $this;
    }
}
