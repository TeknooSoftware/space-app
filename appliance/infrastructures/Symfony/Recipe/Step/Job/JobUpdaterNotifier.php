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

namespace Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Job;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teknoo\East\Paas\Object\Job;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Infrastructures\Symfony\Mercure\JobUrlPublisher;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class JobUpdaterNotifier
{
    public function __construct(
        private JobUrlPublisher $publisher,
        private UrlGeneratorInterface $generator,
        private string $pendingJobRoute,
        private string $getJobRoute,
    ) {
    }

    public function __invoke(
        Project $project,
        Job $job,
        string $newJobId,
    ): static {
        $this->publisher->publish(
            url: $this->generator->generate(
                name: $this->pendingJobRoute,
                parameters: [
                    'newJobId' => $newJobId,
                ],
                referenceType: UrlGeneratorInterface::ABSOLUTE_URL,
            ),
            newJobId: $newJobId,
            jobUrl: $this->generator->generate(
                name: $this->getJobRoute,
                parameters: [
                    'id' => $job->getId(),
                ],
                referenceType: UrlGeneratorInterface::ABSOLUTE_URL,
            ),
            project: $project,
            job: $job,
        );

        return $this;
    }
}
