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

namespace Teknoo\Space\Tests\Unit\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
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
 * @covers \Teknoo\Space\Middleware\HostnameRedirectionMiddleware
 */
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
        self::assertInstanceOf(
            HostnameRedirectionMiddleware::class,
            $this->hostnameRedirectionMiddleware->execute(
                $this->createMock(ClientInterface::class),
                $this->createMock(MessageInterface::class),
                $this->createMock(ManagerInterface::class),
            ),
        );
    }
}
