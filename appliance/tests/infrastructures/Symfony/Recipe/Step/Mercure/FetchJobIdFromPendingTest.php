<?php

/*
 * Teknoo Space.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Recipe\Step\Mercure;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Chunk\FirstChunk;
use Symfony\Component\HttpClient\Chunk\LastChunk;
use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Component\HttpClient\Response\ResponseStream;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\HubRegistry;
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
 * @covers \Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Mercure\FetchJobIdFromPending
 */
class FetchJobIdFromPendingTest extends TestCase
{
    private FetchJobIdFromPending $fetchJobIdFromPending;

    private HubRegistry $hub;

    private UrlGeneratorInterface|MockObject $generator;

    private EventSourceHttpClient|MockObject $sseClient;

    private string $pendingJobRoute;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->hub = new HubRegistry($this->createMock(HubInterface::class));
        $this->generator = $this->createMock(UrlGeneratorInterface::class);
        $this->sseClient = new EventSourceHttpClient(
            $httpClient = $this->createMock(HttpClientInterface::class),
        );

        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::any())
            ->method('getInfo')
            ->willReturnCallback(
                fn ($key) => match ($key) {
                    'http_code' => 200,
                    'response_headers' => [
                        'Content-Type: text/event-stream',
                    ],
                    default => false,
                }
            );

        $httpClient->expects(self::any())
            ->method('request')
            ->willReturn($response);

        $httpClient->expects(self::any())
            ->method('stream')
            ->willReturnCallback(
                function () use ($response) {
                    $generator = function () use ($response): \Generator {
                        yield $response => new FirstChunk();
                        yield $response => new ServerSentEvent(
                            'id: urn:uuid:313a4bdc-bad0-4ead-8513-72f36c19e9b3' . PHP_EOL . 'data: {"foo": "bar"}'
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
        self::assertInstanceOf(
            FetchJobIdFromPending::class,
            ($this->fetchJobIdFromPending)(
                $this->createMock(ManagerInterface::class),
                $this->createMock(ParametersBag::class),
                'foo',
            )
        );
    }
}
