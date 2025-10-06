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

namespace Teknoo\Space\Tests\Unit\Middleware;

use Laminas\Diactoros\Response\RedirectResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Middleware\HostnameRedirectionMiddleware;

/**
 * Class HostnameRedirectionMiddlewareTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(HostnameRedirectionMiddleware::class)]
class HostnameRedirectionMiddlewareTest extends TestCase
{
    private HostnameRedirectionMiddleware $hostnameRedirectionMiddleware;

    private string $allowedHost;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->allowedHost = '42';
        $this->hostnameRedirectionMiddleware = new HostnameRedirectionMiddleware($this->allowedHost);
    }

    public function testExecute(): void
    {
        $this->assertInstanceOf(
            HostnameRedirectionMiddleware::class,
            $this->hostnameRedirectionMiddleware->execute(
                $this->createMock(ClientInterface::class),
                $this->createMock(MessageInterface::class),
                $this->createMock(ManagerInterface::class),
            ),
        );
    }

    public function testExecuteWithAllowedHostExactMatch(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->once())
            ->method('getHost')
            ->willReturn($this->allowedHost);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->never())
            ->method('acceptResponse');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('stop');

        $this->assertInstanceOf(
            HostnameRedirectionMiddleware::class,
            $this->hostnameRedirectionMiddleware->execute(
                $client,
                $request,
                $manager,
            ),
        );
    }

    public function testExecuteWithAllowedHostCaseInsensitive(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->once())
            ->method('getHost')
            ->willReturn(strtoupper($this->allowedHost));

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->never())
            ->method('acceptResponse');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('stop');

        $this->assertInstanceOf(
            HostnameRedirectionMiddleware::class,
            $this->hostnameRedirectionMiddleware->execute(
                $client,
                $request,
                $manager,
            ),
        );
    }

    public function testExecuteWithPrivateIp10(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->once())
            ->method('getHost')
            ->willReturn('10.0.0.1');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->never())
            ->method('acceptResponse');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('stop');

        $this->assertInstanceOf(
            HostnameRedirectionMiddleware::class,
            $this->hostnameRedirectionMiddleware->execute(
                $client,
                $request,
                $manager,
            ),
        );
    }

    public function testExecuteWithPrivateIp172(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->once())
            ->method('getHost')
            ->willReturn('172.16.0.1');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->never())
            ->method('acceptResponse');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('stop');

        $this->assertInstanceOf(
            HostnameRedirectionMiddleware::class,
            $this->hostnameRedirectionMiddleware->execute(
                $client,
                $request,
                $manager,
            ),
        );
    }

    public function testExecuteWithPrivateIp127(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->once())
            ->method('getHost')
            ->willReturn('127.0.0.1');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->never())
            ->method('acceptResponse');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('stop');

        $this->assertInstanceOf(
            HostnameRedirectionMiddleware::class,
            $this->hostnameRedirectionMiddleware->execute(
                $client,
                $request,
                $manager,
            ),
        );
    }

    public function testExecuteWithPrivateIp192(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->once())
            ->method('getHost')
            ->willReturn('192.168.1.1');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->never())
            ->method('acceptResponse');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('stop');

        $this->assertInstanceOf(
            HostnameRedirectionMiddleware::class,
            $this->hostnameRedirectionMiddleware->execute(
                $client,
                $request,
                $manager,
            ),
        );
    }

    public function testExecuteWithRedirection(): void
    {
        $newUri = $this->createMock(UriInterface::class);

        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->once())
            ->method('getHost')
            ->willReturn('example.com');
        $uri->expects($this->once())
            ->method('withHost')
            ->with($this->allowedHost)
            ->willReturn($newUri);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('acceptResponse')
            ->with($this->callback(function ($response) {
                return $response instanceof RedirectResponse
                    && $response->getStatusCode() === 302;
            }));

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('stop');

        $this->assertInstanceOf(
            HostnameRedirectionMiddleware::class,
            $this->hostnameRedirectionMiddleware->execute(
                $client,
                $request,
                $manager,
            ),
        );
    }
}
