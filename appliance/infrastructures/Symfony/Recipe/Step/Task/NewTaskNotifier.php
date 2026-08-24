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

namespace Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Task;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\Exception\ExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Contracts\DTO\NewTaskInterface;
use Teknoo\Space\Contracts\Recipe\Step\Task\NewTaskNotifierInterface;
use Teknoo\Space\Infrastructures\Symfony\Mercure\Exception\OtherException;
use Teknoo\Space\Infrastructures\Symfony\Mercure\Exception\UnavailableException;
use Teknoo\Space\Infrastructures\Symfony\Mercure\JobUrlPublisher;
use Teknoo\Space\Object\DTO\NewJob;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class NewTaskNotifier implements NewTaskNotifierInterface
{
    public function __construct(
        private readonly JobUrlPublisher $publisher,
        private readonly UrlGeneratorInterface $generator,
        private readonly string $pendingTaskRoute,
        private readonly string $projectJobeRoute,
        private readonly string $spaceDashboardRoute,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(
        NewTaskInterface $task,
        ManagerInterface $manager,
    ): NewTaskNotifierInterface {
        try {
            $this->publisher->publish(
                url: $this->generator->generate(
                    name: $this->pendingTaskRoute,
                    parameters: ['taskId' => $task->taskId],
                    referenceType: UrlGeneratorInterface::ABSOLUTE_URL
                ),
                taskId: $task->taskId,
                jobUrl: null,
            );
        } catch (ExceptionInterface $mercureException) {
            $this->logger->critical(
                new UnavailableException(
                    message: 'teknoo.space.error.new_job.mercure_unavailable',
                    code: $mercureException->getCode(),
                    previous: $mercureException,
                )
            );

            if ($task instanceof NewJob) {
                $manager->updateWorkPlan([
                    'route' => $this->projectJobeRoute,
                    'routeParameters' => [
                        'projectId' => $task->projectId,
                        'accountId' => $task->accountId,
                    ],
                ]);
            } else {
                $manager->updateWorkPlan([
                    'route' => $this->spaceDashboardRoute,
                ]);
            }
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
