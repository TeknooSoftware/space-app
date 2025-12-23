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
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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

    private HttpMethodsClientInterface&Stub $httpMethodsClient;

    private ClusterCatalog $clusterCatalog;

    private ResponseFactoryInterface&Stub $responseFactory;

    private StreamFactoryInterface&Stub $streamFactory;

    private UrlGeneratorInterface&Stub $urlGenerator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->httpMethodsClient = $this->createStub(HttpMethodsClientInterface::class);
        $this->responseFactory = $this->createStub(ResponseFactoryInterface::class);
        $this->streamFactory = $this->createStub(StreamFactoryInterface::class);
        $this->urlGenerator = $this->createStub(UrlGeneratorInterface::class);

        $clusterConfig = new ClusterConfig(
            name: 'foo',
            sluggyName: 'foo',
            type: 'foo',
            masterAddress: 'foo',
            storageProvisioner: 'foo',
            dashboardAddress: 'foo',
            kubernetesClient: $this->createStub(Client::class),
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
            $this->streamFactory,
            $this->urlGenerator,
        );
    }

    public function testInvoke(): void
    {
        $sRequest = $this->createStub(ServerRequestInterface::class);
        $sRequest->method('getMethod')->willReturn('GET');

        $finalResponse = $this->createStub(ResponseInterface::class);
        $finalResponse->method('withBody')->willReturnSelf();
        $this->responseFactory
            ->method('createResponse')
            ->willReturn($finalResponse);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getReasonPhrase')->willReturn('foo');
        $response->method('getBody')->willReturn(
            $this->createStub(StreamInterface::class)
        );

        $this->httpMethodsClient
            ->method('send')
            ->willReturn($response);

        $wallet = $this->createStub(AccountWallet::class);
        $wallet
            ->method('has')
            ->willReturn(true);
        $wallet
            ->method('get')
            ->willReturn($this->createStub(AccountEnvironment::class));

        $this->assertInstanceOf(
            DashboardFrame::class,
            ($this->dashboardFrame)(
                manager: $this->createStub(ManagerInterface::class),
                client: $this->createStub(EastClient::class),
                serverRequest: $sRequest,
                user: $this->createStub(User::class),
                clusterCatalog: $this->clusterCatalog,
                clusterName: 'clusterName',
                wildcard: '*',
                account: $this->createStub(Account::class),
                accountWallet: $wallet,
                envName: 'prod',
            )
        );
    }
}
