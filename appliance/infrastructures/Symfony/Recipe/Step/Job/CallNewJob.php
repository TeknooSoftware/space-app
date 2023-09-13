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
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Job;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Contracts\Message\MessageInterface;
use Teknoo\East\Paas\Contracts\Security\EncryptionInterface;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Contracts\Recipe\Step\Job\CallNewJobInterface;
use Teknoo\Space\Object\DTO\NewJob;
use Teknoo\Space\Object\DTO\SpaceProject;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CallNewJob implements CallNewJobInterface
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private ?EncryptionInterface $encryption,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        NewJob $newJob,
        SpaceProject $project,
        ParametersBag $parametersBag,
    ): CallNewJobInterface {
        $dispatching = function (NewJob $newJob): void {
            $this->messageBus->dispatch(
                new Envelope(
                    $newJob->export()
                )
            );
        };

        if (null === $this->encryption) {
            $dispatching($newJob);
        } else {
            /** @var Promise<MessageInterface, mixed, mixed> $promise */
            $promise = new Promise(
                onSuccess: $dispatching,
                onFail: fn (Throwable $error) => throw $error,
            );

            $this->encryption->encrypt(
                data: $newJob,
                promise: $promise,
            );
        }

        $parametersBag->set('newJobId', $newJob->newJobId);
        $parametersBag->set('projectId', $project->getId());

        $manager->updateWorkPlan([
            'routeParameters' => [
                'newJobId' => $newJob->newJobId,
                'projectId' => $project->getId(),
                'projectName' => (string) $project->project,
            ]
        ]);

        return $this;
    }
}
