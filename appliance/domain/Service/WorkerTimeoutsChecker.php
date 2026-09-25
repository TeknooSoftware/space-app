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

namespace Teknoo\Space\Service;

use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Service\Exception\TimeoutTooBigException;

/**
 * To check, when the worker executing jobs starts, the timeouts of external operations (git cloning, image building,
 * deployment, hooks) against the worker's time limit (set on each job by the step `SetTimeLimit`). A timeout bigger
 * than this time limit is useless, the worker will be stopped before reaching it. A job running a hook and then
 * deploying can also be stopped even if each timeout is lower than the time limit, so the biggest hook timeout added
 * to the biggest deployment timeout is also checked (all hooks are not used by each job, so only the biggest one is
 * counted). Unlimited timeouts (null or lower or equal to zero) are ignored, as an unlimited time limit.
 * Each issue is passed to the promise as a `TimeoutTooBigException`, like hooks do with
 * `TimeoutAwareHookInterface::checkTimeLimit()`.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class WorkerTimeoutsChecker
{
    private const string TIME_LIMIT_NAME = 'SPACE_WORKER_TIME_LIMIT';

    /**
     * @param array<string, int|float|null> $timeouts timeouts in seconds, not related to the deployment, indexed by
     *  their env var name
     * @param array<string, int|float|null> $deploymentTimeouts timeouts in seconds of each deployment driver,
     *  indexed by their env var name
     * @param array<string, int|float|null> $hooksTimeouts timeouts in seconds of hooks, indexed by hooks' names
     */
    public function __construct(
        private readonly int $timeLimit,
        private readonly array $timeouts,
        private readonly array $deploymentTimeouts,
        private readonly array $hooksTimeouts,
    ) {
    }

    /**
     * @param array<string, int|float|null> $timeouts
     * @return array{0: string, 1: int|float}|null
     */
    private function findBiggest(array $timeouts): ?array
    {
        $biggest = null;
        foreach ($timeouts as $name => $timeout) {
            if (null === $timeout || $timeout <= 0) {
                continue;
            }

            if (null === $biggest || $timeout > $biggest[1]) {
                $biggest = [$name, $timeout];
            }
        }

        return $biggest;
    }

    /**
     * @return list<string>
     */
    private function listIssues(): array
    {
        if ($this->timeLimit <= 0) {
            return [];
        }

        $timeLimit = self::TIME_LIMIT_NAME;

        $issues = [];
        foreach ([...$this->timeouts, ...$this->deploymentTimeouts] as $name => $timeout) {
            if (null !== $timeout && $timeout > $this->timeLimit) {
                $issues[] = "The timeout `{$name}` ({$timeout}s) is bigger than the worker time limit "
                    . "`{$timeLimit}` ({$this->timeLimit}s), the worker will be stopped before reaching it";
            }
        }

        foreach ($this->hooksTimeouts as $name => $timeout) {
            if (null !== $timeout && $timeout > $this->timeLimit) {
                $issues[] = "The timeout of the hook `{$name}` ({$timeout}s) is bigger than the worker time limit "
                    . "`{$timeLimit}` ({$this->timeLimit}s), the worker will be stopped before reaching it";
            }
        }

        $biggestHook = $this->findBiggest($this->hooksTimeouts);
        $biggestDeployment = $this->findBiggest($this->deploymentTimeouts);
        if (null === $biggestHook || null === $biggestDeployment) {
            return $issues;
        }

        [$hookName, $hookTimeout] = $biggestHook;
        [$deploymentName, $deploymentTimeout] = $biggestDeployment;
        $total = $hookTimeout + $deploymentTimeout;
        if ($total > $this->timeLimit) {
            $issues[] = "The biggest hook timeout (`{$hookName}`, {$hookTimeout}s) added to the biggest deployment "
                . "timeout (`{$deploymentName}`, {$deploymentTimeout}s) gives {$total}s, bigger than the worker time "
                . "limit `{$timeLimit}` ({$this->timeLimit}s), a job running this hook may be stopped before the end "
                . "of its deployment";
        }

        return $issues;
    }

    /**
     * The promise is failed with a `TimeoutTooBigException` for each issue found, so it must allow reuse to be
     * notified of all of them. It is succeeded, without argument, when no issue is found.
     *
     * @param PromiseInterface<mixed, mixed> $promise
     */
    public function check(PromiseInterface $promise): self
    {
        $issues = $this->listIssues();
        if (empty($issues)) {
            $promise->success();

            return $this;
        }

        foreach ($issues as $issue) {
            $promise->fail(new TimeoutTooBigException($issue));
        }

        return $this;
    }
}
