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
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Contracts\Security\EncryptionInterface;
use Teknoo\East\Paas\Contracts\Security\SensitiveContentInterface;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Contracts\DTO\NewTaskInterface;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Task\CallNewTask;
use Teknoo\Space\Object\DTO\JobVar;
use Teknoo\Space\Object\DTO\NewJob;
use Teknoo\Space\Object\DTO\SpaceProject;

/**
 * Class CallNewTaskTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(CallNewTask::class)]
class CallNewTaskTest extends TestCase
{
    private CallNewTask $callNewTask;

    private MessageBusInterface&Stub $messageBus;

    private EncryptionInterface&Stub $encryption;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->messageBus = $this->createStub(MessageBusInterface::class);
        $this->encryption = $this->createStub(EncryptionInterface::class);
        $this->callNewTask = new CallNewTask(
            $this->messageBus,
            $this->encryption,
        );
    }

    /**
     * A task which is not a NewJob : it carries no project at all.
     * `createStub(NewTaskInterface::class)` is deliberately avoided, the interface declares property
     * hooks (`public string $taskId { get; }`) and they are satisfied here by plain public properties.
     */
    private function createNonJobTask(string $taskId, ?string $accountId): NewTaskInterface
    {
        return new class ($taskId, $accountId) implements NewTaskInterface {
            /**
             * @param array<object> $variables
             */
            public function __construct(
                public string $taskId,
                public ?string $accountId,
                public array $variables = [],
            ) {
            }

            public function export(): NewTaskInterface
            {
                return $this;
            }

            public function getMessage(): string
            {
                return '[]';
            }

            /**
             * @return array<string, string|int|bool|array<int|string, string|int|bool>>
             */
            public function toArray(): array
            {
                return ['taskId' => $this->taskId];
            }

            public function getContent(): string
            {
                return $this->getMessage();
            }

            public function getEncryptionAlgorithm(): ?string
            {
                return null;
            }

            public function cloneWith(string $content, ?string $encryptionAlgorithm): SensitiveContentInterface
            {
                return $this;
            }
        };
    }

    public function testInvoke(): void
    {
        $newJob = new NewJob(
            taskId: 'foo',
            variables: [
                new JobVar('foo'),
            ],
        );

        $this->messageBus
            ->method('dispatch')
            ->willReturn(new Envelope($newJob));

        $this->assertInstanceOf(
            CallNewTask::class,
            ($this->callNewTask)(
                $this->createStub(ManagerInterface::class),
                $newJob,
                $this->createStub(ParametersBag::class),
                new SpaceProject($this->createStub(Project::class)),
            )
        );
    }

    public function testInvokeWithANewJobPublishesTheProjectParameters(): void
    {
        $newJob = new NewJob(taskId: 'foo', accountId: 'an-account');

        $project = $this->createStub(Project::class);
        $project->method('getId')->willReturn('a-project');

        $bag = new ParametersBag();

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with([
                'routeParameters' => [
                    'taskId' => 'foo',
                    'projectId' => 'a-project',
                    'projectName' => (string) $project,
                ],
            ]);

        ($this->callNewTask)($manager, $newJob, $bag, new SpaceProject($project));

        $this->assertEquals(
            [
                'taskId' => 'foo',
                'accountId' => 'an-account',
                'projectId' => 'a-project',
            ],
            $bag->transform(),
        );
    }

    public function testInvokeWithANewJobWithoutProjectThrowsAnException(): void
    {
        $this->expectException(RuntimeException::class);

        ($this->callNewTask)(
            $this->createStub(ManagerInterface::class),
            new NewJob(taskId: 'foo'),
            new ParametersBag(),
        );
    }

    public function testInvokeWithANonJobTaskSkipsTheProjectParameters(): void
    {
        $bag = new ParametersBag();

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with([
                'routeParameters' => [
                    'taskId' => 'bar',
                ],
            ]);

        $this->assertInstanceOf(
            CallNewTask::class,
            ($this->callNewTask)(
                $manager,
                $this->createNonJobTask('bar', 'an-account'),
                $bag,
            )
        );

        $this->assertEquals(
            [
                'taskId' => 'bar',
                'accountId' => 'an-account',
            ],
            $bag->transform(),
        );
    }

    public function testInvokeWithoutEncryptionDispatchesTheExportedTask(): void
    {
        $task = $this->createNonJobTask('bar', null);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(
                static fn (Envelope $envelope): bool => $envelope->getMessage() === $task,
            ))
            ->willReturn(new Envelope($task));

        $callNewTask = new CallNewTask($messageBus, null);

        $this->assertInstanceOf(
            CallNewTask::class,
            $callNewTask(
                $this->createStub(ManagerInterface::class),
                $task,
                new ParametersBag(),
            )
        );
    }
}
