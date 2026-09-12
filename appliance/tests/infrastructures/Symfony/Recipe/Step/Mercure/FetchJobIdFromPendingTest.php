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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Recipe\Step\Mercure;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Chunk\ErrorChunk;
use Symfony\Component\HttpClient\Chunk\FirstChunk;
use Symfony\Component\HttpClient\Chunk\LastChunk;
use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Component\HttpClient\Response\ResponseStream;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\HubRegistry;
use Symfony\Component\Mercure\Jwt\TokenFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\ChunkInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Mercure\Exception\ExceedLimitException;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Mercure\Exception\SSEClosedException;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Mercure\FetchJobIdFromPending;

use const PHP_EOL;

/**
 * Class FetchJobIdFromPendingTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(FetchJobIdFromPending::class)]
class FetchJobIdFromPendingTest extends TestCase
{
    private HubRegistry $hub;

    private UrlGeneratorInterface&Stub $generator;

    private EventSourceHttpClient $sseClient;

    private HttpClientInterface&Stub $httpClient;

    private ResponseInterface&Stub $response;

    private string $pendingTaskRoute;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $hubMock = $this->createStub(HubInterface::class);
        $tokenFactory = $this->createStub(TokenFactoryInterface::class);
        $tokenFactory->method('create')->willReturn('mock-jwt-token');
        $hubMock->method('getFactory')->willReturn($tokenFactory);

        $this->hub = new HubRegistry($hubMock);
        $this->generator = $this->createStub(UrlGeneratorInterface::class);
        $this->sseClient = new EventSourceHttpClient(
            $this->httpClient = $this->createStub(HttpClientInterface::class),
        );

        $this->response = $this->createStub(ResponseInterface::class);
        $this->response
            ->method('getInfo')
            ->willReturnCallback(
                fn (?string $key): array|int|false => match ($key) {
                    'http_code' => 200,
                    'response_headers' => [
                        'Content-Type: text/event-stream',
                    ],
                    default => false,
                }
            );

        $this->httpClient
            ->method('request')
            ->willReturn($this->response);

        $this->pendingTaskRoute = 'foo';
    }

    /**
     * Each callable feeds one call to the http client's stream() (the last one is reused for further calls).
     *
     * @param callable(ResponseInterface): Generator<ResponseInterface, ChunkInterface> ...$calls
     */
    private function prepareStream(callable ...$calls): void
    {
        $callCounter = 0;
        $this->httpClient
            ->method('stream')
            ->willReturnCallback(
                function () use (&$callCounter, $calls): ResponseStream {
                    $chunks = $calls[min($callCounter++, count($calls) - 1)];

                    return new ResponseStream(
                        $chunks($this->response),
                    );
                }
            );
    }

    private function createEvent(): ServerSentEvent
    {
        return new ServerSentEvent(
            ':' . PHP_EOL
                . 'id: urn:uuid:3212d4b5-f4b8-4322-b5a4-5c49160c3283' . PHP_EOL
                . 'data: {"foo":"bar"}' . PHP_EOL
                . PHP_EOL
        );
    }

    private function buildStep(
        int $maxLoopInSSE = 123,
        int $maxChunkCount = 456,
        bool $mercureEnabled = true,
    ): FetchJobIdFromPending {
        return new FetchJobIdFromPending(
            $this->hub,
            $this->generator,
            $this->sseClient,
            $this->pendingTaskRoute,
            $maxLoopInSSE,
            $maxChunkCount,
            $mercureEnabled,
        );
    }

    public function testInvoke(): void
    {
        $this->prepareStream(
            function (ResponseInterface $response): Generator {
                yield $response => new FirstChunk();
                yield $response => $this->createEvent();
                yield $response => new LastChunk();
            }
        );

        $bag = $this->createMock(ParametersBag::class);
        $bag
            ->expects($this->once())
            ->method('set')
            ->with('taskResult', ['foo' => 'bar'])
            ->willReturnSelf();

        $this->assertInstanceOf(
            FetchJobIdFromPending::class,
            ($this->buildStep())(
                $this->createStub(ManagerInterface::class),
                $bag,
                'foo',
            )
        );
    }

    public function testInvokeWithMercureDisabled(): void
    {
        $bag = $this->createMock(ParametersBag::class);
        $bag
            ->expects($this->once())
            ->method('set')
            ->with(
                'taskResult',
                [
                    'task_id' => 'foo',
                    'error_code' => 500,
                    'error_message' => 'teknoo.space.error.job.pending.mercure_disabled',
                ],
            )
            ->willReturnSelf();

        $this->assertInstanceOf(
            FetchJobIdFromPending::class,
            ($this->buildStep(mercureEnabled: false))(
                $this->createStub(ManagerInterface::class),
                $bag,
                'foo',
            )
        );
    }

    public function testInvokeWhenChunkLimitIsExceeded(): void
    {
        $this->prepareStream(
            function (ResponseInterface $response): Generator {
                yield $response => new FirstChunk();
                yield $response => $this->createEvent();
                yield $response => new LastChunk();
            }
        );

        $this->expectException(ExceedLimitException::class);
        $this->expectExceptionMessage('teknoo.space.error.job.pending.exceed_sse_chunk_limit');
        ($this->buildStep(maxChunkCount: 0))(
            $this->createStub(ManagerInterface::class),
            $this->createStub(ParametersBag::class),
            'foo',
        );
    }

    public function testInvokeWithATimeoutBeforeTheEvent(): void
    {
        $this->prepareStream(
            function (ResponseInterface $response): Generator {
                yield $response => new FirstChunk();
                yield $response => new ErrorChunk(0, 'timeout');
            },
            function (ResponseInterface $response): Generator {
                yield $response => $this->createEvent();
                yield $response => new LastChunk();
            }
        );

        $bag = $this->createMock(ParametersBag::class);
        $bag
            ->expects($this->once())
            ->method('set')
            ->with('taskResult', ['foo' => 'bar'])
            ->willReturnSelf();

        $this->assertInstanceOf(
            FetchJobIdFromPending::class,
            ($this->buildStep())(
                $this->createStub(ManagerInterface::class),
                $bag,
                'foo',
            )
        );
    }

    public function testInvokeWhenTheStreamIsClosedWithoutEvent(): void
    {
        $this->prepareStream(
            function (ResponseInterface $response): Generator {
                yield $response => new FirstChunk();
                yield $response => new LastChunk();
            }
        );

        $this->expectException(SSEClosedException::class);
        ($this->buildStep())(
            $this->createStub(ManagerInterface::class),
            $this->createStub(ParametersBag::class),
            'foo',
        );
    }

    public function testInvokeWhenRetryLimitIsExceeded(): void
    {
        $this->prepareStream(
            function (ResponseInterface $response): Generator {
                yield $response => new FirstChunk();
                yield $response => new ErrorChunk(0, 'timeout');
            }
        );

        $this->expectException(ExceedLimitException::class);
        $this->expectExceptionMessage('teknoo.space.error.job.pending.exceed_sse_retry_limit');
        ($this->buildStep(maxLoopInSSE: 0))(
            $this->createStub(ManagerInterface::class),
            $this->createStub(ParametersBag::class),
            'foo',
        );
    }
}
