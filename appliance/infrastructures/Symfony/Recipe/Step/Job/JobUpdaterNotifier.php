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

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teknoo\East\Paas\Object\Job;
use Teknoo\Space\Infrastructures\Symfony\Mercure\JobUrlPublisher;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
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
        Job $job,
        string $newJobId,
    ): static {
        $this->publisher->publish(
            $this->generator->generate(
                $this->pendingJobRoute,
                [
                    'newJobId' => $newJobId,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL,
            ),
            $newJobId,
            $this->generator->generate(
                $this->getJobRoute,
                [
                    'id' => $job->getId(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL,
            ),
        );

        return $this;
    }
}
