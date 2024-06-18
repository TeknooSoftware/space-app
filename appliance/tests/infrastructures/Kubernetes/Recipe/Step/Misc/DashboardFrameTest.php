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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Kubernetes\Recipe\Step\Misc;

use Http\Client\Common\HttpMethodsClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Client\ClientInterface as EastClient;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Kubernetes\Client;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Misc\DashboardFrame;
use Teknoo\Space\Object\Config\Cluster as ClusterConfig;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\Persisted\AccountEnvironment;

/**
 * Class DashboardFrameTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Misc\DashboardFrame
 */
class DashboardFrameTest extends TestCase
{
    private DashboardFrame $dashboardFrame;

    private HttpMethodsClientInterface|MockObject $httpMethodsClient;

    private ClusterCatalog $catalog;

    private ResponseFactoryInterface|MockObject $responseFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->httpMethodsClient = $this->createMock(HttpMethodsClientInterface::class);
        $this->responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $clusterConfig = new ClusterConfig(
            name: 'foo',
            sluggyName: 'foo',
            type: 'foo',
            masterAddress: 'foo',
            storageProvisioner: 'foo',
            dashboardAddress: 'foo',
            kubernetesClient: $this->createMock(Client::class),
            token: 'foo',
            supportRegistry: true,
            useHnc: false,
        );

        $this->catalog = new ClusterCatalog(
            ['clusterName' => $clusterConfig],
            ['cluster-name' => 'clusterName'],
        );

        $this->dashboardFrame = new DashboardFrame(
            $this->catalog,
            $this->httpMethodsClient,
            $this->responseFactory,
        );
    }

    public function testInvoke(): void
    {
        $sRequest = $this->createMock(ServerRequestInterface::class);
        $sRequest->expects($this->any())->method('getMethod')->willReturn('GET');

        $finalResponse = $this->createMock(ResponseInterface::class);
        $finalResponse->expects($this->any())->method('withBody')->willReturnSelf();
        $this->responseFactory
            ->expects($this->any())
            ->method('createResponse')
            ->willReturn($finalResponse);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())->method('getStatusCode')->willReturn(200);
        $response->expects($this->any())->method('getReasonPhrase')->willReturn('foo');
        $response->expects($this->any())->method('getBody')->willReturn(
            $this->createMock(StreamInterface::class)
        );

        $this->httpMethodsClient
            ->expects($this->any())
            ->method('send')
            ->willReturn($response);

        $wallet = $this->createMock(AccountWallet::class);
        $wallet->expects($this->any())
            ->method('has')
            ->willReturn(true);
        $wallet->expects($this->any())
            ->method('get')
            ->willReturn($this->createMock(AccountEnvironment::class));

        self::assertInstanceOf(
            DashboardFrame::class,
            ($this->dashboardFrame)(
                manager: $this->createMock(ManagerInterface::class),
                client: $this->createMock(EastClient::class),
                serverRequest: $sRequest,
                user: $this->createMock(User::class),
                clusterName: 'clusterName',
                wildcard: '*',
                account: $this->createMock(Account::class),
                accountWallet: $wallet,
                envName: 'prod',
            )
        );
    }
}
