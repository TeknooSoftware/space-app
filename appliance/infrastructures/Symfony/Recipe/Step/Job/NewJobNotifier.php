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

use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\Exception\ExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Contracts\Recipe\Step\Job\NewJobNotifierInterface;
use Teknoo\Space\Infrastructures\Symfony\Mercure\Exception\OtherException;
use Teknoo\Space\Infrastructures\Symfony\Mercure\Exception\UnavailableException;
use Teknoo\Space\Infrastructures\Symfony\Mercure\JobUrlPublisher;
use Teknoo\Space\Object\DTO\NewJob;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class NewJobNotifier implements NewJobNotifierInterface
{
    public function __construct(
        private JobUrlPublisher $publisher,
        private UrlGeneratorInterface $generator,
        private string $pendingJobRoute,
        private string $projectJobeRoute,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(
        NewJob $newJob,
        ManagerInterface $manager,
    ): NewJobNotifierInterface {
        try {
            $this->publisher->publish(
                url: $this->generator->generate(
                    name: $this->pendingJobRoute,
                    parameters: ['newJobId' => $newJob->newJobId],
                    referenceType: UrlGeneratorInterface::ABSOLUTE_URL
                ),
                newJobId: $newJob->newJobId,
                jobUrl: null,
            );
        } catch (ExceptionInterface $mercureException) {
            $this->logger->critical(
                new  UnavailableException(
                    message: 'teknoo.space.error.new_job.mercure_unavailable',
                    code: $mercureException->getCode(),
                    previous: $mercureException,
                )
            );

            $manager->updateWorkPlan([
                'route' => $this->projectJobeRoute,
                'routeParameters' => [
                    'projectId' => $newJob->projectId,
                    'accountId' => $newJob->accountId,
                ],
            ]);
        } catch (Throwable $mainException) {
            throw new OtherException(
                message: 'teknoo.space.error.new_job.error',
                code: $mainException->getCode(),
                previous: $mainException,
            );
        }

        return $this;
    }
}
