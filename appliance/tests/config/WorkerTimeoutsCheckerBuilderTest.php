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

namespace Teknoo\Space\Tests\Unit\Config;

use ArrayObject;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Service\Exception\TimeoutTooBigException;
use Teknoo\Space\Service\WorkerTimeoutsChecker;
use Throwable;

/**
 * Exercises the `WorkerTimeoutsChecker` builder closure in `appliance/config/di.services.php`, fed by the time
 * limit, the timeouts parameters and the hooks definitions. `config/` is outside the coverage scope, so this test
 * documents/guards the builder behaviour rather than adding coverage.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversNothing]
class WorkerTimeoutsCheckerBuilderTest extends TestCase
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

    /**
     * @param array<string, mixed> $parameters
     */
    private function buildChecker(int $timeLimit, array $parameters): WorkerTimeoutsChecker
    {
        /** @var array<string, callable> $config */
        $config = require __DIR__ . '/../../config/di.services.php';
        $builder = $config[WorkerTimeoutsChecker::class];

        /** @var array<string, callable> $hookConfig */
        $hookConfig = require __DIR__ . '/../../config/di.hook.php';
        $parameters['teknoo.space.hooks_collection.default_timeout']
            = $hookConfig['teknoo.space.hooks_collection.default_timeout'];

        $parameters['teknoo.east.paas.di.worker.time_limit'] = static fn (): int => $timeLimit;

        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturnCallback(
                static fn (string $id): bool => isset($parameters[$id]),
            );
        $container->method('get')
            ->willReturnCallback(
                static fn (string $id): mixed => $parameters[$id] ?? null,
            );

        return $builder($container);
    }

    public function testBuildWithAllParameters(): void
    {
        $checker = $this->buildChecker(
            1000,
            [
                'teknoo.east.paas.git.cloning.timeout' => '240',
                'teknoo.east.paas.img_builder.build.timeout' => '1800',
                'teknoo.east.paas.docker-compose.timeout' => '900',
                'teknoo.east.paas.kubernetes.timeout' => '3',
                'teknoo.space.hooks_collection.definitions' => new ArrayObject([
                    ['name' => 'composer', 'type' => 'composer', 'command' => ['composer']],
                    ['name' => 'npm', 'type' => 'npm', 'command' => ['npm'], 'timeout' => 60],
                    ['type' => 'make', 'command' => ['make']],
                ]),
            ],
        );

        $this->assertIssues(
            [
                'The timeout `SPACE_IMG_BUILDER_TIMEOUT` (1800s) is bigger than the worker time limit '
                    . '`SPACE_WORKER_TIME_LIMIT` (1000s), the worker will be stopped before reaching it',
                'The biggest hook timeout (`composer`, 240s) added to the biggest deployment timeout '
                    . '(`SPACE_DC_TIMEOUT`, 900s) gives 1140s, bigger than the worker time limit '
                    . '`SPACE_WORKER_TIME_LIMIT` (1000s), a job running this hook may be stopped before the end of '
                    . 'its deployment',
            ],
            $checker,
        );
    }

    public function testBuildWithMissingParameters(): void
    {
        $checker = $this->buildChecker(
            100,
            [
                'teknoo.east.paas.kubernetes.timeout' => '3',
            ],
        );

        $this->assertIssues([], $checker);
    }
}
