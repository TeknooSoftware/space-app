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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Chunk\FirstChunk;
use Symfony\Component\HttpClient\Chunk\LastChunk;
use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Component\HttpClient\Response\ResponseStream;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\HubRegistry;
use Symfony\Component\Mercure\Jwt\TokenFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
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
    private FetchJobIdFromPending $fetchJobIdFromPending;

    private HubRegistry $hub;

    private UrlGeneratorInterface&Stub $generator;

    private EventSourceHttpClient $sseClient;

    private string $pendingJobRoute;

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
            $httpClient = $this->createStub(HttpClientInterface::class),
        );

        $response = $this->createStub(ResponseInterface::class);
        $response
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

        $httpClient
            ->method('request')
            ->willReturn($response);

        $httpClient
            ->method('stream')
            ->willReturnCallback(
                function () use ($response): \Symfony\Component\HttpClient\Response\ResponseStream {
                    $generator = function () use ($response): \Generator {
                        yield $response => new FirstChunk();
                        yield $response => new ServerSentEvent(
                            ':' . PHP_EOL
                                . 'id: urn:uuid:3212d4b5-f4b8-4322-b5a4-5c49160c3283' . PHP_EOL
                                . 'data: {"foo":"bar"}' . PHP_EOL
                                . PHP_EOL
                        );
                        yield $response => new LastChunk();
                    };

                    return new ResponseStream(
                        $generator(),
                    );
                }
            );

        $this->pendingJobRoute = 'foo';
        $this->fetchJobIdFromPending = new FetchJobIdFromPending(
            $this->hub,
            $this->generator,
            $this->sseClient,
            $this->pendingJobRoute,
            123,
            456
        );
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            FetchJobIdFromPending::class,
            ($this->fetchJobIdFromPending)(
                $this->createStub(ManagerInterface::class),
                $this->createStub(ParametersBag::class),
                'foo',
            )
        );
    }
}
