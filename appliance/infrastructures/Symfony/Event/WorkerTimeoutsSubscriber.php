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

namespace Teknoo\Space\Infrastructures\Symfony\Event;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Service\WorkerTimeoutsChecker;
use Throwable;

use function in_array;
use function is_array;

/**
 * To print on the standard output of the worker consuming the `execute_job` transport, when it starts, a warning for
 * each timeout the worker's time limit makes useless (see WorkerTimeoutsChecker).
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class WorkerTimeoutsSubscriber implements EventSubscriberInterface
{
    private const string CONSUME_COMMAND = 'messenger:consume';

    private const string EXECUTE_JOB_TRANSPORT = 'execute_job';

    public function __construct(
        private readonly WorkerTimeoutsChecker $checker,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => [
                ['checkTimeouts'],
            ],
        ];
    }

    private function isConsumingJobs(ConsoleCommandEvent $event): bool
    {
        if (self::CONSUME_COMMAND !== $event->getCommand()?->getName()) {
            return false;
        }

        //The input is bound to the command's definition before the dispatching of this event
        $input = $event->getInput();
        if ($input->hasOption('all') && true === $input->getOption('all')) {
            return true;
        }

        if (!$input->hasArgument('receivers')) {
            return false;
        }

        $receivers = $input->getArgument('receivers');

        return is_array($receivers) && in_array(self::EXECUTE_JOB_TRANSPORT, $receivers, true);
    }

    public function checkTimeouts(ConsoleCommandEvent $event): self
    {
        if (!$this->isConsumingJobs($event)) {
            return $this;
        }

        $warnings = [];

        /** @var Promise<mixed, mixed, mixed> $promise */
        $promise = new Promise(
            onFail: static function (Throwable $error) use (&$warnings): void {
                $warnings[] = $error->getMessage();
            },
        );

        //The checker fails the promise for each issue, all warnings are printed in a single block
        $promise->allowReuse();

        $this->checker->check($promise);

        if (empty($warnings)) {
            return $this;
        }

        (new SymfonyStyle($event->getInput(), $event->getOutput()))->warning($warnings);

        return $this;
    }
}
