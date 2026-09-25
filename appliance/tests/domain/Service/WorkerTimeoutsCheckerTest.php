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

namespace Teknoo\Space\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Service\Exception\TimeoutTooBigException;
use Teknoo\Space\Service\WorkerTimeoutsChecker;
use Throwable;

/**
 * Class WorkerTimeoutsCheckerTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(WorkerTimeoutsChecker::class)]
class WorkerTimeoutsCheckerTest extends TestCase
{
    /**
     * @param list<string> $expected
     */
    private function assertIssues(array $expected, WorkerTimeoutsChecker $checker): void
    {
        $issues = [];
        $succeeded = false;

        /** @var Promise<mixed, mixed, mixed> $promise */
        $promise = new Promise(
            onSuccess: static function () use (&$succeeded): void {
                $succeeded = true;
            },
            onFail: static function (Throwable $error) use (&$issues): void {
                self::assertInstanceOf(TimeoutTooBigException::class, $error);
                $issues[] = $error->getMessage();
            },
        );
        $promise->allowReuse();

        $this->assertInstanceOf(
            WorkerTimeoutsChecker::class,
            $checker->check($promise),
        );

        $this->assertSame($expected, $issues);
        $this->assertSame(empty($expected), $succeeded);
    }

    public function testCheckWithoutTimeLimit(): void
    {
        $checker = new WorkerTimeoutsChecker(
            timeLimit: 0,
            timeouts: ['SPACE_GIT_TIMEOUT' => 600],
            deploymentTimeouts: ['SPACE_DC_TIMEOUT' => 900],
            hooksTimeouts: ['composer' => 240],
        );

        $this->assertIssues([], $checker);
    }

    public function testCheckWhenAllTimeoutsFit(): void
    {
        $checker = new WorkerTimeoutsChecker(
            timeLimit: 1200,
            timeouts: ['SPACE_GIT_TIMEOUT' => 240.0, 'SPACE_IMG_BUILDER_TIMEOUT' => 600.0],
            deploymentTimeouts: ['SPACE_DC_TIMEOUT' => 900.0, 'SPACE_KUBERNETES_CLIENT_TIMEOUT' => 3.0],
            hooksTimeouts: ['composer' => 240.0, 'npm' => 300.0],
        );

        $this->assertIssues([], $checker);
    }

    public function testCheckIgnoresUnlimitedTimeouts(): void
    {
        $checker = new WorkerTimeoutsChecker(
            timeLimit: 300,
            timeouts: ['SPACE_GIT_TIMEOUT' => null, 'SPACE_IMG_BUILDER_TIMEOUT' => 0.0],
            deploymentTimeouts: ['SPACE_DC_TIMEOUT' => -1.0, 'SPACE_KUBERNETES_CLIENT_TIMEOUT' => null],
            hooksTimeouts: ['composer' => 0.0, 'npm' => null],
        );

        $this->assertIssues([], $checker);
    }

    public function testCheckTimeoutsBiggerThanTimeLimit(): void
    {
        $checker = new WorkerTimeoutsChecker(
            timeLimit: 300,
            timeouts: ['SPACE_GIT_TIMEOUT' => 240.0, 'SPACE_IMG_BUILDER_TIMEOUT' => 600.0],
            deploymentTimeouts: ['SPACE_DC_TIMEOUT' => 900.0, 'SPACE_KUBERNETES_CLIENT_TIMEOUT' => 3.0],
            hooksTimeouts: [],
        );

        $this->assertIssues(
            [
                'The timeout `SPACE_IMG_BUILDER_TIMEOUT` (600s) is bigger than the worker time limit '
                    . '`SPACE_WORKER_TIME_LIMIT` (300s), the worker will be stopped before reaching it',
                'The timeout `SPACE_DC_TIMEOUT` (900s) is bigger than the worker time limit '
                    . '`SPACE_WORKER_TIME_LIMIT` (300s), the worker will be stopped before reaching it',
            ],
            $checker,
        );
    }

    public function testCheckHookTimeoutBiggerThanTimeLimit(): void
    {
        $checker = new WorkerTimeoutsChecker(
            timeLimit: 300,
            timeouts: [],
            deploymentTimeouts: [],
            hooksTimeouts: ['composer' => 240.0, 'npm' => 400.5],
        );

        $this->assertIssues(
            [
                'The timeout of the hook `npm` (400.5s) is bigger than the worker time limit '
                    . '`SPACE_WORKER_TIME_LIMIT` (300s), the worker will be stopped before reaching it',
            ],
            $checker,
        );
    }

    public function testCheckBiggestHookAddedToBiggestDeploymentBiggerThanTimeLimit(): void
    {
        $checker = new WorkerTimeoutsChecker(
            timeLimit: 1000,
            timeouts: ['SPACE_GIT_TIMEOUT' => 240.0],
            deploymentTimeouts: ['SPACE_KUBERNETES_CLIENT_TIMEOUT' => 3.0, 'SPACE_DC_TIMEOUT' => 900.0],
            hooksTimeouts: ['composer' => 240.0, 'make' => 60.0, 'npm' => null],
        );

        $this->assertIssues(
            [
                'The biggest hook timeout (`composer`, 240s) added to the biggest deployment timeout '
                    . '(`SPACE_DC_TIMEOUT`, 900s) gives 1140s, bigger than the worker time limit '
                    . '`SPACE_WORKER_TIME_LIMIT` (1000s), a job running this hook may be stopped before the end of '
                    . 'its deployment',
            ],
            $checker,
        );
    }

    public function testCheckReportsEachIssue(): void
    {
        $checker = new WorkerTimeoutsChecker(
            timeLimit: 300,
            timeouts: [],
            deploymentTimeouts: ['SPACE_DC_TIMEOUT' => 900],
            hooksTimeouts: ['composer' => 400],
        );

        $this->assertIssues(
            [
                'The timeout `SPACE_DC_TIMEOUT` (900s) is bigger than the worker time limit '
                    . '`SPACE_WORKER_TIME_LIMIT` (300s), the worker will be stopped before reaching it',
                'The timeout of the hook `composer` (400s) is bigger than the worker time limit '
                    . '`SPACE_WORKER_TIME_LIMIT` (300s), the worker will be stopped before reaching it',
                'The biggest hook timeout (`composer`, 400s) added to the biggest deployment timeout '
                    . '(`SPACE_DC_TIMEOUT`, 900s) gives 1300s, bigger than the worker time limit '
                    . '`SPACE_WORKER_TIME_LIMIT` (300s), a job running this hook may be stopped before the end of '
                    . 'its deployment',
            ],
            $checker,
        );
    }

    public function testCheckWithoutHookIgnoresTheSum(): void
    {
        $checker = new WorkerTimeoutsChecker(
            timeLimit: 1000,
            timeouts: [],
            deploymentTimeouts: ['SPACE_DC_TIMEOUT' => 900.0],
            hooksTimeouts: ['composer' => null],
        );

        $this->assertIssues([], $checker);
    }

    public function testCheckWithoutDeploymentTimeoutIgnoresTheSum(): void
    {
        $checker = new WorkerTimeoutsChecker(
            timeLimit: 300,
            timeouts: ['SPACE_GIT_TIMEOUT' => 240.0],
            deploymentTimeouts: ['SPACE_DC_TIMEOUT' => 0.0, 'SPACE_KUBERNETES_CLIENT_TIMEOUT' => null],
            hooksTimeouts: ['composer' => 240.0],
        );

        $this->assertIssues([], $checker);
    }
}
