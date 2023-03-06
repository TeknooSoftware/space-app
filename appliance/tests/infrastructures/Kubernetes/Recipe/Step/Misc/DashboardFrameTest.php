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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Infrastructures\Kubernetes\Recipe\Step\Misc;

use Http\Client\Common\HttpMethodsClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Teknoo\East\Foundation\Client\ClientInterface as EastClient;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Misc\DashboardFrame;
use Teknoo\Space\Object\Persisted\AccountCredential;

/**
 * Class DashboardFrameTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Misc\DashboardFrame
 */
class DashboardFrameTest extends TestCase
{
    private DashboardFrame $dashboardFrame;

    private string $dashboardUrl;

    private HttpMethodsClientInterface|MockObject $httpMethodsClient;

    private string $clusterToken;

    private ResponseFactoryInterface|MockObject $responseFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->dashboardUrl = 'foo';
        $this->httpMethodsClient = $this->createMock(HttpMethodsClientInterface::class);
        $this->clusterToken = 'bar';
        $this->responseFactory = $this->createMock(ResponseFactoryInterface::class);

        $this->dashboardFrame = new DashboardFrame(
            $this->dashboardUrl,
            $this->httpMethodsClient,
            $this->clusterToken,
            $this->responseFactory,
        );
    }

    public function testInvoke(): void
    {
        $sRequest = $this->createMock(ServerRequestInterface::class);
        $sRequest->expects(self::any())->method('getMethod')->willReturn('GET');

        $finalResponse = $this->createMock(ResponseInterface::class);
        $finalResponse->expects(self::any())->method('withBody')->willReturnSelf();
        $this->responseFactory
            ->expects(self::any())
            ->method('createResponse')
            ->willReturn($finalResponse);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::any())->method('getStatusCode')->willReturn(200);
        $response->expects(self::any())->method('getReasonPhrase')->willReturn('foo');
        $response->expects(self::any())->method('getBody')->willReturn(
            $this->createMock(StreamInterface::class)
        );

        $this->httpMethodsClient
            ->expects(self::any())
            ->method('send')
            ->willReturn($response);

        self::assertInstanceOf(
            DashboardFrame::class,
            ($this->dashboardFrame)(
                $this->createMock(ManagerInterface::class),
                $this->createMock(EastClient::class),
                $sRequest,
                '*',
                $this->createMock(Account::class),
                $this->createMock(AccountCredential::class),
            )
        );
    }
}
