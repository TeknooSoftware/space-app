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

namespace Teknoo\Space\Recipe\Step\Job;

use RuntimeException;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Environment;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Object\DTO\NewJob;
use Teknoo\Space\Object\DTO\SpaceProject;

use function array_merge;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class PrepareNewJobForm
{
    /**
     * @param array<string, mixed> $formOptions
     */
    public function __invoke(
        ManagerInterface $manager,
        Project|SpaceProject $projectInstance,
        NewJob $newJobInstance,
        ParametersBag $bag,
        ?string $formActionRoute = null,
        array $formOptions = [],
    ): self {
        if ($projectInstance instanceof SpaceProject) {
            $projectInstance = $projectInstance->project;
        }

        if (!$projectInstance->isRunnable()) {
            throw new RuntimeException('Project is not fully configured');
        }

        $newJobInstance->projectId = $projectInstance->getId();
        $newJobInstance->accountId = $projectInstance->getAccount()->getId();

        $environmentsList = [];
        $projectInstance->listMeYourEnvironments(
            static function (Environment $env) use (&$environmentsList) {
                $environmentsList[(string) $env] = (string) $env;
            }
        );

        $manager->updateWorkPlan([
            'formOptions' => array_merge(
                $formOptions,
                [
                    'environmentsList' => $environmentsList,
                ],
            )
        ]);

        $bag->set('project', $projectInstance);

        if (!empty($formActionRoute)) {
            $bag->set('formActionRoute', $formActionRoute);
            $bag->set('formActionRouteParams', ['projectId' => $projectInstance->getId()]);
        }

        return $this;
    }
}
