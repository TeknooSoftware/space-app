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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Messenger\Handler;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Teknoo\East\Foundation\Time\SleepServiceInterface;
use Teknoo\East\FoundationBundle\Messenger\Client;
use Teknoo\East\FoundationBundle\Messenger\Executor;
use Teknoo\East\Foundation\Http\Message\MessageFactoryInterface;
use Teknoo\East\Paas\Contracts\Security\EncryptionInterface;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Contracts\Object\EncryptableVariableInterface;
use Teknoo\Space\Infrastructures\Symfony\Messenger\Handler\Exception\BadEncryptionConfigurationException;
use Teknoo\Space\Object\DTO\JobVar;
use Throwable;
use Teknoo\Space\Infrastructures\Symfony\Mercure\Notifier\TaskError;
use Teknoo\Space\Infrastructures\Symfony\Messenger\Handler\NewTaskHandler;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Space\Object\DTO\NewJob;
use Teknoo\Space\Service\NewTaskRecipeRegistry;
use Teknoo\Space\Service\PersistedVariableEncryption;

/**
 * Class NewTaskHandlerTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(NewTaskHandler::class)]
class NewTaskHandlerTest extends TestCase
{
    private NewTaskHandler $newTaskHandler;

    private Executor&Stub $executor;

    private BaseRecipeInterface&Stub $recipe;

    private NewTaskRecipeRegistry $recipeRegistry;

    private MessageFactoryInterface&Stub $messageFactory;

    private StreamFactoryInterface&Stub $streamFactory;

    private Client&Stub $client;

    private LoggerInterface&Stub $logger;

    private TaskError&Stub $jobError;

    private EncryptionInterface&Stub $encryption;

    private SleepServiceInterface&Stub $sleepService;

    private PersistedVariableEncryption&Stub $persistedVariableEncryption;

    private int $waitingTimeSecond;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->executor = $this->createStub(Executor::class);
        $this->recipe = $this->createStub(BaseRecipeInterface::class);
        $this->recipeRegistry = (new NewTaskRecipeRegistry())->register(NewJob::class, $this->recipe);
        $this->messageFactory = $this->createStub(MessageFactoryInterface::class);
        $this->streamFactory = $this->createStub(StreamFactoryInterface::class);
        $this->client = $this->createStub(Client::class);
        $this->logger = $this->createStub(LoggerInterface::class);
        $this->jobError = $this->createStub(TaskError::class);
        $this->encryption = $this->createStub(EncryptionInterface::class);
        $this->sleepService = $this->createStub(SleepServiceInterface::class);
        $this->persistedVariableEncryption = $this->createStub(PersistedVariableEncryption::class);
        $this->waitingTimeSecond = 1;
        $this->newTaskHandler = new NewTaskHandler(
            $this->executor,
            $this->recipeRegistry,
            $this->messageFactory,
            $this->streamFactory,
            $this->client,
            $this->logger,
            $this->jobError,
            $this->encryption,
            $this->sleepService,
            $this->persistedVariableEncryption,
            $this->waitingTimeSecond,
        );
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            NewTaskHandler::class,
            ($this->newTaskHandler)(
                new NewJob(taskId: 'foo'),
            )
        );
    }

    public function testInvokeWithoutEncryptionResolvesTheRecipeFromTheRegistry(): void
    {
        $executor = $this->createMock(Executor::class);
        $executor->expects($this->once())
            ->method('execute')
            ->with(
                $this->recipe,
                $this->anything(),
                $this->anything(),
                $this->callback(
                    static fn (array $workPlan): bool => 'foo' === $workPlan['taskId']
                        && ['task_id' => 'foo'] === $workPlan['extra']
                        && $workPlan[NewJob::class] instanceof NewJob,
                ),
            );

        $handler = new NewTaskHandler(
            $executor,
            $this->recipeRegistry,
            $this->messageFactory,
            $this->streamFactory,
            $this->client,
            $this->logger,
            $this->jobError,
            null,
            $this->sleepService,
            $this->persistedVariableEncryption,
            0,
        );

        $this->assertInstanceOf(
            NewTaskHandler::class,
            $handler(new NewJob(taskId: 'foo')),
        );
    }

    public function testInvokeWithoutRegisteredRecipeGoesToTheErrorChannel(): void
    {
        $jobError = $this->createMock(TaskError::class);
        $jobError->expects($this->once())
            ->method('process')
            ->with($this->anything(), 'foo');

        $handler = new NewTaskHandler(
            $this->executor,
            new NewTaskRecipeRegistry(),
            $this->messageFactory,
            $this->streamFactory,
            $this->client,
            $this->logger,
            $jobError,
            null,
            $this->sleepService,
            $this->persistedVariableEncryption,
            0,
        );

        $this->assertInstanceOf(
            NewTaskHandler::class,
            $handler(new NewJob(taskId: 'foo')),
        );
    }

    private function createHandlerWithoutEncryption(
        Executor $executor,
        TaskError $jobError,
        PersistedVariableEncryption $encryptionService,
        StreamFactoryInterface $streamFactory,
    ): NewTaskHandler {
        $message = $this->createStub(MessageInterface::class);
        $message->method('withBody')->willReturnSelf();
        $message->method('withAddedHeader')->willReturnSelf();
        $messageFactory = $this->createStub(MessageFactoryInterface::class);
        $messageFactory->method('createMessage')->willReturn($message);

        return new NewTaskHandler(
            $executor,
            $this->recipeRegistry,
            $messageFactory,
            $streamFactory,
            $this->client,
            $this->logger,
            $jobError,
            null,
            $this->sleepService,
            $encryptionService,
            0,
        );
    }

    private function createStreamFactoryExpecting(string $json): StreamFactoryInterface
    {
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects($this->once())
            ->method('createStream')
            ->with($json);

        return $streamFactory;
    }

    public function testInvokeWithPlainVariables(): void
    {
        $executor = $this->createMock(Executor::class);
        $executor->expects($this->once())->method('execute');

        $jobError = $this->createMock(TaskError::class);
        $jobError->expects($this->never())->method('process');

        $encryptionService = $this->createMock(PersistedVariableEncryption::class);
        $encryptionService->expects($this->never())->method('decrypt');

        $handler = $this->createHandlerWithoutEncryption(
            $executor,
            $jobError,
            $encryptionService,
            $this->createStreamFactoryExpecting('{"a":"b"}'),
        );

        $this->assertInstanceOf(
            NewTaskHandler::class,
            $handler(new NewJob(taskId: 'foo', variables: [new JobVar(name: 'a', value: 'b')])),
        );
    }

    public function testInvokeWithEncryptedVariables(): void
    {
        $executor = $this->createMock(Executor::class);
        $executor->expects($this->once())->method('execute');

        $jobError = $this->createMock(TaskError::class);
        $jobError->expects($this->never())->method('process');

        $encryptionService = $this->createMock(PersistedVariableEncryption::class);
        $encryptionService->expects($this->once())
            ->method('decrypt')
            ->willReturnCallback(
                static function (
                    EncryptableVariableInterface $variable,
                    PromiseInterface $promise,
                ) use ($encryptionService): PersistedVariableEncryption {
                    $promise->success(new JobVar(name: 'a', value: 'decrypted'));

                    return $encryptionService;
                },
            );

        $handler = $this->createHandlerWithoutEncryption(
            $executor,
            $jobError,
            $encryptionService,
            $this->createStreamFactoryExpecting('{"a":"decrypted"}'),
        );

        $this->assertInstanceOf(
            NewTaskHandler::class,
            $handler(
                new NewJob(
                    taskId: 'foo',
                    variables: [new JobVar(name: 'a', value: 'encrypted', encryptionAlgorithm: 'aes')],
                ),
            ),
        );
    }

    public function testInvokeWithoutEncryptionButEncryptedTask(): void
    {
        $executor = $this->createMock(Executor::class);
        $executor->expects($this->never())->method('execute');

        $jobError = $this->createMock(TaskError::class);
        $jobError->expects($this->once())
            ->method('process')
            ->with($this->isInstanceOf(BadEncryptionConfigurationException::class), 'foo');

        $encryptionService = $this->createMock(PersistedVariableEncryption::class);
        $encryptionService->expects($this->never())->method('decrypt');

        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects($this->never())->method('createStream');

        $handler = $this->createHandlerWithoutEncryption(
            $executor,
            $jobError,
            $encryptionService,
            $streamFactory,
        );

        $this->assertInstanceOf(
            NewTaskHandler::class,
            $handler((new NewJob(taskId: 'foo'))->cloneWith('content', 'aes-256-cbc')),
        );
    }
}
