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

namespace Teknoo\Space\Recipe\Step\SpaceProject;

use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Environment;
use Teknoo\East\Paas\Object\Project;
use Teknoo\East\Paas\Object\Project\Executable;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\Persisted\ProjectMetadata;

use function array_merge;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class WorkplanInit
{
    /**
     * @param array<string, mixed> $formOptions
     */
    public function __invoke(
        ManagerInterface $manager,
        SpaceProject $spaceProject,
        ?Project $projectObject = null,
        ?ProjectMetadata $metadata = null,
        bool $populateFormOptions = false,
        array $formOptions = [],
    ): self {
        $projectInstance = $spaceProject->project ?? $projectObject;

        $workPlan = [
            Project::class => $projectInstance,
            ProjectMetadata::class => $spaceProject->projectMetadata ?? $metadata,
        ];

        if (null !== $projectInstance && $populateFormOptions) {
            $environmentsList = [];

            $projectInstance->isInState(
                [
                    Executable::class,
                ],
                static function () use ($projectInstance, &$environmentsList): void {
                    $projectInstance->listMeYourEnvironments(
                        static function (Environment $env) use (&$environmentsList) {
                            $environmentsList[(string) $env] = (string) $env;
                        }
                    );
                }
            );

            $workPlan['formOptions'] = array_merge(
                $formOptions,
                [
                    'environmentsList' => $environmentsList,
                ],
            );
        }

        $manager->updateWorkPlan($workPlan);

        return $this;
    }
}
