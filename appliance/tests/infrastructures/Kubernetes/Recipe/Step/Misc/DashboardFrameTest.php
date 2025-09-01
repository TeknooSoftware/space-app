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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Kubernetes\Recipe\Step\Misc;

use Http\Client\Common\HttpMethodsClientInterface;
use PHPUnit\Framework\Attributes\CoversClass;
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
 */
#[CoversClass(DashboardFrame::class)]
class DashboardFrameTest extends TestCase
{
    private DashboardFrame $dashboardFrame;

    private HttpMethodsClientInterface&MockObject $httpMethodsClient;

    private ClusterCatalog $clusterCatalog;

    private ResponseFactoryInterface&MockObject $responseFactory;

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
            isExternal: false,
        );

        $this->clusterCatalog = new ClusterCatalog(
            ['clusterName' => $clusterConfig],
            ['cluster-name' => 'clusterName'],
        );

        $this->dashboardFrame = new DashboardFrame(
            $this->httpMethodsClient,
            $this->responseFactory,
        );
    }

    public function testInvoke(): void
    {
        $sRequest = $this->createMock(ServerRequestInterface::class);
        $sRequest->method('getMethod')->willReturn('GET');

        $finalResponse = $this->createMock(ResponseInterface::class);
        $finalResponse->method('withBody')->willReturnSelf();
        $this->responseFactory
            ->method('createResponse')
            ->willReturn($finalResponse);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getReasonPhrase')->willReturn('foo');
        $response->method('getBody')->willReturn(
            $this->createMock(StreamInterface::class)
        );

        $this->httpMethodsClient
            ->method('send')
            ->willReturn($response);

        $wallet = $this->createMock(AccountWallet::class);
        $wallet
            ->method('has')
            ->willReturn(true);
        $wallet
            ->method('get')
            ->willReturn($this->createMock(AccountEnvironment::class));

        $this->assertInstanceOf(
            DashboardFrame::class,
            ($this->dashboardFrame)(
                manager: $this->createMock(ManagerInterface::class),
                client: $this->createMock(EastClient::class),
                serverRequest: $sRequest,
                user: $this->createMock(User::class),
                clusterCatalog: $this->clusterCatalog,
                clusterName: 'clusterName',
                wildcard: '*',
                account: $this->createMock(Account::class),
                accountWallet: $wallet,
                envName: 'prod',
            )
        );
    }
}
