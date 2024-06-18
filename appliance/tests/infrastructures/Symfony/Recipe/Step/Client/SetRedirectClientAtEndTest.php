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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Recipe\Step\Client;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Client\SetRedirectClientAtEnd;

/**
 * Class SetRedirectClientAtEndTest.
 *
 * @license http://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Client\SetRedirectClientAtEnd
 */
class SetRedirectClientAtEndTest extends TestCase
{
    private SetRedirectClientAtEnd $setRedirectClientAtEnd;

    private ResponseFactoryInterface|MockObject $responseFactory;

    private UrlGeneratorInterface|MockObject $router;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $this->router = $this->createMock(UrlGeneratorInterface::class);
        $this->setRedirectClientAtEnd = new SetRedirectClientAtEnd($this->responseFactory, $this->router);
    }

    public function testInvoke(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())
            ->method('withHeader')
            ->willReturnSelf();

        $this->responseFactory->expects($this->any())
            ->method('createResponse')
            ->willReturn($response);

        self::assertInstanceOf(
            SetRedirectClientAtEnd::class,
            ($this->setRedirectClientAtEnd)(
                $this->createMock(ManagerInterface::class),
                $this->createMock(ClientInterface::class),
                'foo',
                123
            )
        );
    }
}
