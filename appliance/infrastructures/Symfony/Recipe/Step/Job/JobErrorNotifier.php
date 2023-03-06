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
use Teknoo\Space\Infrastructures\Symfony\Mercure\JobErrorPublisher;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class JobErrorNotifier
{
    public function __construct(
        private JobErrorPublisher $publisher,
        private UrlGeneratorInterface $generator,
        private string $pendingJobRoute,
    ) {
    }

    public function __invoke(
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
