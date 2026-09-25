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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Teknoo\Space\Infrastructures\Symfony\Event\WorkerTimeoutsSubscriber;
use Teknoo\Space\Service\WorkerTimeoutsChecker;

use function preg_replace;

/**
 * Class WorkerTimeoutsSubscriberTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(WorkerTimeoutsSubscriber::class)]
class WorkerTimeoutsSubscriberTest extends TestCase
{
    private BufferedOutput $output;

    protected function setUp(): void
    {
        parent::setUp();

        $this->output = new BufferedOutput();
    }

    private function buildSubscriber(int $timeLimit): WorkerTimeoutsSubscriber
    {
        return new WorkerTimeoutsSubscriber(
            new WorkerTimeoutsChecker(
                timeLimit: $timeLimit,
                timeouts: ['SPACE_GIT_TIMEOUT' => 240.0],
                deploymentTimeouts: ['SPACE_DC_TIMEOUT' => 900.0],
                hooksTimeouts: ['composer' => 240.0],
            ),
        );
    }

    private function buildConsumeCommand(): Command
    {
        $command = new Command('messenger:consume');
        $command->addArgument('receivers', InputArgument::IS_ARRAY);
        $command->addOption('all', null, InputOption::VALUE_NONE);

        return $command;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function buildEvent(?Command $command, array $parameters): ConsoleCommandEvent
    {
        $input = new ArrayInput($parameters, $command?->getDefinition());

        return new ConsoleCommandEvent($command, $input, $this->output);
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
            [
                ConsoleEvents::COMMAND => [
                    ['checkTimeouts'],
                ],
            ],
            WorkerTimeoutsSubscriber::getSubscribedEvents(),
        );
    }

    public function testCheckTimeoutsWithoutCommand(): void
    {
        $this->assertInstanceOf(
            WorkerTimeoutsSubscriber::class,
            $this->buildSubscriber(300)->checkTimeouts(
                $this->buildEvent(null, []),
            ),
        );

        $this->assertSame('', $this->output->fetch());
    }

    public function testCheckTimeoutsWithAnotherCommand(): void
    {
        $this->assertInstanceOf(
            WorkerTimeoutsSubscriber::class,
            $this->buildSubscriber(300)->checkTimeouts(
                $this->buildEvent(new Command('cache:clear'), []),
            ),
        );

        $this->assertSame('', $this->output->fetch());
    }

    public function testCheckTimeoutsWithoutReceiversArgument(): void
    {
        $this->assertInstanceOf(
            WorkerTimeoutsSubscriber::class,
            $this->buildSubscriber(300)->checkTimeouts(
                $this->buildEvent(new Command('messenger:consume'), []),
            ),
        );

        $this->assertSame('', $this->output->fetch());
    }

    public function testCheckTimeoutsWhenConsumingOtherTransports(): void
    {
        $this->assertInstanceOf(
            WorkerTimeoutsSubscriber::class,
            $this->buildSubscriber(300)->checkTimeouts(
                $this->buildEvent($this->buildConsumeCommand(), ['receivers' => ['new_task', 'job_done']]),
            ),
        );

        $this->assertSame('', $this->output->fetch());
    }

    public function testCheckTimeoutsWhenConsumingJobs(): void
    {
        $this->assertInstanceOf(
            WorkerTimeoutsSubscriber::class,
            $this->buildSubscriber(300)->checkTimeouts(
                $this->buildEvent($this->buildConsumeCommand(), ['receivers' => ['execute_job']]),
            ),
        );

        //The block is wrapped according to the terminal's width
        $content = (string) preg_replace('/\s+/', ' ', $this->output->fetch());
        $this->assertStringContainsString('[WARNING]', $content);
        $this->assertStringContainsString(
            'The timeout `SPACE_DC_TIMEOUT` (900s) is bigger than the worker time limit '
                . '`SPACE_WORKER_TIME_LIMIT` (300s)',
            $content,
        );
        $this->assertStringContainsString('(`SPACE_DC_TIMEOUT`, 900s) gives 1140s', $content);
        $this->assertStringNotContainsString('`SPACE_GIT_TIMEOUT`', $content);
    }

    public function testCheckTimeoutsWhenConsumingAllTransports(): void
    {
        $this->assertInstanceOf(
            WorkerTimeoutsSubscriber::class,
            $this->buildSubscriber(300)->checkTimeouts(
                $this->buildEvent($this->buildConsumeCommand(), ['--all' => true]),
            ),
        );

        $this->assertStringContainsString('[WARNING]', $this->output->fetch());
    }

    public function testCheckTimeoutsWhenConsumingJobsWithoutIssue(): void
    {
        $this->assertInstanceOf(
            WorkerTimeoutsSubscriber::class,
            $this->buildSubscriber(1200)->checkTimeouts(
                $this->buildEvent($this->buildConsumeCommand(), ['receivers' => ['new_task', 'execute_job']]),
            ),
        );

        $this->assertSame('', $this->output->fetch());
    }
}
