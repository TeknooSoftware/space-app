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

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Contracts\Security\SensitiveContentInterface;
use Teknoo\East\Paas\Contracts\Security\EncryptionInterface;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Contracts\DTO\NewTaskInterface;
use Teknoo\Space\Contracts\Recipe\Step\Task\CallNewTaskInterface;
use Teknoo\Space\Object\DTO\NewJob;
use Teknoo\Space\Object\DTO\SpaceProject;
use RuntimeException;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CallNewTask implements CallNewTaskInterface
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly ?EncryptionInterface $encryption,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        NewTaskInterface $newTask,
        ParametersBag $parametersBag,
        ?SpaceProject $project = null,
    ): CallNewTaskInterface {
        $dispatching = function (NewTaskInterface $newTask): void {
            $this->messageBus->dispatch(
                new Envelope(
                    $newTask->export()
                )
            );
        };

        if (null === $this->encryption) {
            $dispatching($newTask);
        } else {
            /** @var Promise<SensitiveContentInterface, mixed, mixed> $promise */
            $promise = new Promise(
                onSuccess: $dispatching,
                onFail: fn (Throwable $error) => throw $error,
            );

            $this->encryption->encrypt(
                data: $newTask,
                promise: $promise,
            );
        }

        $parametersBag->set('taskId', $newTask->taskId);
        $parametersBag->set('accountId', $newTask->accountId);

        $routeParameters = [
            'taskId' => $newTask->taskId,
        ];

        if ($newTask instanceof NewJob) {
            if (!$project instanceof SpaceProject) {
                throw new RuntimeException('teknoo.space.error.call_new_task.missing_project');
            }

            $parametersBag->set('projectId', $project->getId());

            $routeParameters['projectId'] = $project->getId();
            $routeParameters['projectName'] = (string) $project->project;
        }

        $manager->updateWorkPlan([
            'routeParameters' => $routeParameters,
        ]);

        return $this;
    }
}
