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
use RuntimeException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Teknoo\East\FoundationBundle\Messenger\Client;
use Teknoo\East\FoundationBundle\Messenger\Executor;
use Teknoo\East\Foundation\Http\Message\MessageFactoryInterface;
use Teknoo\East\Paas\Contracts\Security\EncryptionInterface;
use Teknoo\East\Paas\Infrastructures\Symfony\Messenger\Message\MessageJob;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Infrastructures\Symfony\Messenger\Handler\Exception\BadEncryptionConfigurationException;
use Teknoo\Space\Infrastructures\Symfony\Messenger\Handler\RunJobHandler;
use Throwable;

/**
 * Class RunJobHandlerTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(RunJobHandler::class)]
class RunJobHandlerTest extends TestCase
{
    private RunJobHandler $runJobHandler;

    private Executor&Stub $executor;

    private BaseRecipeInterface&Stub $recipe;

    private MessageFactoryInterface&Stub $messageFactory;

    private StreamFactoryInterface&Stub $streamFactory;

    private Client&Stub $client;

    private LoggerInterface&Stub $logger;

    private EncryptionInterface&Stub $encryption;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->executor = $this->createStub(Executor::class);
        $this->recipe = $this->createStub(BaseRecipeInterface::class);
        $this->messageFactory = $this->createStub(MessageFactoryInterface::class);
        $this->streamFactory = $this->createStub(StreamFactoryInterface::class);
        $this->client = $this->createStub(Client::class);
        $this->logger = $this->createStub(LoggerInterface::class);
        $this->encryption = $this->createStub(EncryptionInterface::class);

        $this->runJobHandler = new RunJobHandler(
            $this->executor,
            $this->recipe,
            $this->messageFactory,
            $this->streamFactory,
            $this->client,
            $this->logger,
            $this->encryption,
        );
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            RunJobHandler::class,
            ($this->runJobHandler)(
                $this->createStub(MessageJob::class),
            )
        );
    }

    /**
     * @var array<int, Throwable>
     */
    private array $recordedErrors = [];

    private function createClientRecordingErrors(): Client
    {
        $this->recordedErrors = [];
        $client = $this->createStub(Client::class);
        $client->method('errorInRequest')->willReturnCallback(
            function (Throwable $error) use ($client): Client {
                $this->recordedErrors[] = $error;

                return $client;
            },
        );

        return $client;
    }

    private function createHandler(
        Executor $executor,
        Client $client,
        ?EncryptionInterface $encryption,
    ): RunJobHandler {
        $message = $this->createStub(MessageInterface::class);
        $message->method('withBody')->willReturnSelf();
        $message->method('withAddedHeader')->willReturnSelf();
        $messageFactory = $this->createStub(MessageFactoryInterface::class);
        $messageFactory->method('createMessage')->willReturn($message);

        return new RunJobHandler(
            $executor,
            $this->recipe,
            $messageFactory,
            $this->streamFactory,
            $client,
            $this->logger,
            $encryption,
        );
    }

    public function testInvokeWithEncryptionWhenDecryptionSucceeds(): void
    {
        $message = new MessageJob('pid', 'env', 'jid', '{}', 'aes-256-cbc');
        $decrypted = new MessageJob('pid', 'env', 'jid', '{}');

        $executor = $this->createMock(Executor::class);
        $executor->expects($this->once())
            ->method('execute')
            ->with(
                $this->recipe,
                $this->isInstanceOf(MessageInterface::class),
                $this->isInstanceOf(Client::class),
                ['projectId' => 'pid', 'envName' => 'env', 'jobId' => 'jid'],
            );

        $encryption = $this->createMock(EncryptionInterface::class);
        $encryption->expects($this->once())
            ->method('decrypt')
            ->with($message, $this->isInstanceOf(PromiseInterface::class))
            ->willReturnCallback(
                static function ($data, PromiseInterface $promise) use ($encryption, $decrypted): EncryptionInterface {
                    $promise->success($decrypted);

                    return $encryption;
                },
            );

        $client = $this->createClientRecordingErrors();
        $handler = $this->createHandler($executor, $client, $encryption);

        $this->assertInstanceOf(RunJobHandler::class, $handler($message));
        $this->assertSame([], $this->recordedErrors);
    }

    public function testInvokeWithEncryptionWhenDecryptionFails(): void
    {
        $message = new MessageJob('pid', 'env', 'jid', '{}', 'aes-256-cbc');

        $executor = $this->createMock(Executor::class);
        $executor->expects($this->never())->method('execute');

        $encryption = $this->createMock(EncryptionInterface::class);
        $encryption->expects($this->once())
            ->method('decrypt')
            ->willReturnCallback(
                static function ($data, PromiseInterface $promise) use ($encryption): EncryptionInterface {
                    $promise->fail(new RuntimeException('decrypt failed', 42));

                    return $encryption;
                },
            );

        $client = $this->createClientRecordingErrors();
        $handler = $this->createHandler($executor, $client, $encryption);

        $this->assertInstanceOf(RunJobHandler::class, $handler($message));
        $this->assertCount(1, $this->recordedErrors);
        $this->assertInstanceOf(UnrecoverableMessageHandlingException::class, $this->recordedErrors[0]);
        $this->assertSame('decrypt failed', $this->recordedErrors[0]->getMessage());
        $this->assertSame(42, $this->recordedErrors[0]->getCode());
        $this->assertInstanceOf(RuntimeException::class, $this->recordedErrors[0]->getPrevious());
    }

    public function testInvokeWithoutEncryptionButEncryptedMessage(): void
    {
        $message = new MessageJob('pid', 'env', 'jid', '{}', 'aes-256-cbc');

        $executor = $this->createMock(Executor::class);
        $executor->expects($this->never())->method('execute');

        $client = $this->createClientRecordingErrors();
        $handler = $this->createHandler($executor, $client, null);

        $this->assertInstanceOf(RunJobHandler::class, $handler($message));
        $this->assertCount(1, $this->recordedErrors);
        $this->assertInstanceOf(UnrecoverableMessageHandlingException::class, $this->recordedErrors[0]);
        $this->assertInstanceOf(BadEncryptionConfigurationException::class, $this->recordedErrors[0]->getPrevious());
    }

    public function testInvokeWithoutEncryptionWhenExecutorFails(): void
    {
        $message = new MessageJob('pid', 'env', 'jid', '{}');

        $executor = $this->createMock(Executor::class);
        $executor->expects($this->once())
            ->method('execute')
            ->willThrowException(new RuntimeException('executor failed'));

        $client = $this->createClientRecordingErrors();
        $handler = $this->createHandler($executor, $client, null);

        $this->assertInstanceOf(RunJobHandler::class, $handler($message));
        $this->assertCount(1, $this->recordedErrors);
        $this->assertInstanceOf(UnrecoverableMessageHandlingException::class, $this->recordedErrors[0]);
        $this->assertSame('executor failed', $this->recordedErrors[0]->getMessage());
    }
}
