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

namespace Teknoo\Space\Infrastructures\Symfony\Mercure\Notifier;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teknoo\Space\Infrastructures\Symfony\Mercure\JobErrorPublisher;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class JobError
{
    public function __construct(
        private readonly JobErrorPublisher $publisher,
        private readonly UrlGeneratorInterface $generator,
        private readonly string $pendingJobRoute,
    ) {
    }

    public function process(
        Throwable $error,
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
            $error,
        );

        return $this;
    }
}
