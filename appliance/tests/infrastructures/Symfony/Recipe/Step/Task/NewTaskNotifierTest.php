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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Recipe\Step\Task;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Mercure\Exception\RuntimeException as MercureRuntimeException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Contracts\DTO\NewTaskInterface;
use Teknoo\Space\Infrastructures\Symfony\Mercure\Exception\OtherException;
use Teknoo\Space\Infrastructures\Symfony\Mercure\Exception\UnavailableException;
use Teknoo\Space\Infrastructures\Symfony\Mercure\TaskUrlPublisher;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Task\NewTaskNotifier;
use Teknoo\Space\Object\DTO\JobVar;
use Teknoo\Space\Object\DTO\NewJob;

/**
 * Class NewTaskNotifierTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(NewTaskNotifier::class)]
class NewTaskNotifierTest extends TestCase
{
    private NewTaskNotifier $newTaskNotifier;

    private TaskUrlPublisher&MockObject $publisher;

    private UrlGeneratorInterface&Stub $generator;

    private LoggerInterface&MockObject $logger;

    private string $pendingTaskRoute;

    private string $spaceDashoardRoute;

    private string $listJobRoute;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->publisher = $this->createMock(TaskUrlPublisher::class);
        $this->generator = $this->createStub(UrlGeneratorInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->pendingTaskRoute = 'pending_route';
        $this->listJobRoute = 'list_job_route';
        $this->spaceDashoardRoute = 'dashboard_route';
        $this->newTaskNotifier = new NewTaskNotifier(
            $this->publisher,
            $this->generator,
            $this->pendingTaskRoute,
            $this->listJobRoute,
            $this->spaceDashoardRoute,
            $this->logger,
        );
    }

    public function testInvoke(): void
    {
        $newJob = new NewJob(
            taskId: 'foo',
            variables: [
                new JobVar('foo'),
            ],
        );

        $this->generator
            ->method('generate')
            ->willReturn('https://foo/pending/foo');

        $this->publisher
            ->expects($this->once())
            ->method('publish')
            ->with('https://foo/pending/foo', 'foo', null);

        $this->logger
            ->expects($this->never())
            ->method('critical');

        $this->assertInstanceOf(
            NewTaskNotifier::class,
            ($this->newTaskNotifier)(
                $newJob,
                $this->createStub(ManagerInterface::class),
            )
        );
    }

    public function testInvokeWhenMercureIsUnavailableForANewJob(): void
    {
        $newJob = new NewJob(taskId: 'foo', projectId: 'project-id', accountId: 'account-id');

        $this->publisher
            ->expects($this->once())
            ->method('publish')
            ->willThrowException(new MercureRuntimeException('hub down', 503));

        $this->logger
            ->expects($this->once())
            ->method('critical')
            ->with($this->isInstanceOf(UnavailableException::class));

        $manager = $this->createMock(ManagerInterface::class);
        $manager
            ->expects($this->once())
            ->method('updateWorkPlan')
            ->with([
                'route' => 'list_job_route',
                'routeParameters' => [
                    'projectId' => 'project-id',
                    'accountId' => 'account-id',
                ],
            ])
            ->willReturnSelf();

        $this->assertInstanceOf(
            NewTaskNotifier::class,
            ($this->newTaskNotifier)(
                $newJob,
                $manager,
            )
        );
    }

    public function testInvokeWhenMercureIsUnavailableForAnotherTask(): void
    {
        $task = $this->createStub(NewTaskInterface::class);

        $this->publisher
            ->expects($this->once())
            ->method('publish')
            ->willThrowException(new MercureRuntimeException('hub down', 503));

        $this->logger
            ->expects($this->once())
            ->method('critical')
            ->with($this->isInstanceOf(UnavailableException::class));

        $manager = $this->createMock(ManagerInterface::class);
        $manager
            ->expects($this->once())
            ->method('updateWorkPlan')
            ->with(['route' => 'dashboard_route'])
            ->willReturnSelf();

        $this->assertInstanceOf(
            NewTaskNotifier::class,
            ($this->newTaskNotifier)(
                $task,
                $manager,
            )
        );
    }

    public function testInvokeWhenPublishingFailsForAnotherReason(): void
    {
        $this->publisher
            ->expects($this->once())
            ->method('publish')
            ->willThrowException(new RuntimeException('boom', 42));

        $this->logger
            ->expects($this->never())
            ->method('critical');

        $this->expectException(OtherException::class);
        $this->expectExceptionCode(42);
        ($this->newTaskNotifier)(
            new NewJob(taskId: 'foo'),
            $this->createStub(ManagerInterface::class),
        );
    }
}
