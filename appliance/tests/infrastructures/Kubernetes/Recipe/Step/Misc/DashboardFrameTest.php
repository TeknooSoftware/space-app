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

use BadMethodCallException;
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
use RuntimeException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Client\ClientInterface as EastClient;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Kubernetes\Client;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Misc\DashboardFrame;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\DockerComposeCluster;
use Teknoo\Space\Object\Config\Exception\UnsupportedClusterTypeException;
use Teknoo\Space\Object\Config\KubernetesCluster as ClusterConfig;
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

    private EngineInterface&Stub $template;

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
        $this->template = $this->createStub(EngineInterface::class);

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
            $this->template,
        );
    }

    private function createServerRequest(): ServerRequestInterface&Stub
    {
        $sRequest = $this->createStub(ServerRequestInterface::class);
        $sRequest->method('getMethod')->willReturn('GET');

        return $sRequest;
    }

    private function createAdmin(): User&Stub
    {
        $user = $this->createStub(User::class);
        $user->method('getRoles')->willReturn(['ROLE_ADMIN']);

        return $user;
    }

    /**
     * @param array<string, string[]> $headers
     */
    private function prepareDashboardResponse(string $expectedUri, array $headers = []): ResponseInterface&MockObject
    {
        $finalResponse = $this->createMock(ResponseInterface::class);
        $finalResponse->method('withBody')->willReturnSelf();
        $finalResponse->expects(empty($headers) ? $this->never() : $this->once())
            ->method('withHeader')
            ->willReturnSelf();
        $this->responseFactory
            ->method('createResponse')
            ->willReturn($finalResponse);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getReasonPhrase')->willReturn('foo');
        $response->method('getBody')->willReturn(
            $this->createStub(StreamInterface::class)
        );
        $response->method('getHeader')
            ->willReturnCallback(fn (string $name): array => $headers[$name] ?? []);

        $this->httpMethodsClient
            ->method('send')
            ->willReturnCallback(
                function (string $method, string $uri) use ($expectedUri, $response): ResponseInterface {
                    $this->assertSame('GET', $method);
                    $this->assertSame($expectedUri, $uri);

                    return $response;
                }
            );

        return $finalResponse;
    }

    private function createWallet(bool $has, bool $withEnvironment): AccountWallet&Stub
    {
        $environment = null;
        if ($withEnvironment) {
            $environment = $this->createStub(AccountEnvironment::class);
            $environment->method('getNamespace')->willReturn('space-ns');
            $environment->method('getToken')->willReturn('env-token');
        }

        $wallet = $this->createStub(AccountWallet::class);
        $wallet->method('has')->willReturn($has);
        $wallet->method('get')->willReturn($environment);

        return $wallet;
    }

    public function testInvokeForNonAdminWithDefaultWildcard(): void
    {
        $finalResponse = $this->prepareDashboardResponse(
            'foo#/workloads?namespace=space-ns',
            ['content-type' => ['text/html']],
        );

        $client = $this->createMock(EastClient::class);
        $client->expects($this->once())
            ->method('acceptResponse')
            ->with($finalResponse)
            ->willReturnSelf();

        $this->assertInstanceOf(
            DashboardFrame::class,
            ($this->dashboardFrame)(
                manager: $this->createStub(ManagerInterface::class),
                client: $client,
                serverRequest: $this->createServerRequest(),
                user: $this->createStub(User::class),
                clusterCatalog: $this->clusterCatalog,
                clusterName: 'clusterName',
                wildcard: '',
                account: $this->createStub(Account::class),
                accountWallet: $this->createWallet(true, true),
                envName: 'prod',
            )
        );
    }

    public function testInvokeForAdminWithAnchoredWildcard(): void
    {
        $this->prepareDashboardResponse('foo#/workloads?namespace=_all');

        $this->assertInstanceOf(
            DashboardFrame::class,
            ($this->dashboardFrame)(
                manager: $this->createStub(ManagerInterface::class),
                client: $this->createStub(EastClient::class),
                serverRequest: $this->createServerRequest(),
                user: $this->createAdmin(),
                clusterCatalog: $this->clusterCatalog,
                clusterName: 'clusterName',
                wildcard: '#/workloads',
            )
        );
    }

    public function testInvokeForAdminWithConfigJson(): void
    {
        $this->prepareDashboardResponse('fooassets/config/config.json');

        $this->assertInstanceOf(
            DashboardFrame::class,
            ($this->dashboardFrame)(
                manager: $this->createStub(ManagerInterface::class),
                client: $this->createStub(EastClient::class),
                serverRequest: $this->createServerRequest(),
                user: $this->createAdmin(),
                clusterCatalog: $this->clusterCatalog,
                clusterName: 'clusterName',
                wildcard: 'config/config.json',
            )
        );
    }

    public function testInvokeForAssetsConfigJsonReturnsNotFound(): void
    {
        $this->streamFactory->method('createStream')
            ->willReturn($this->createStub(StreamInterface::class));

        $client = $this->createMock(EastClient::class);
        $client->expects($this->once())
            ->method('acceptResponse')
            ->with($this->callback(fn (ResponseInterface $response): bool => 404 === $response->getStatusCode()))
            ->willReturnSelf();

        $this->assertInstanceOf(
            DashboardFrame::class,
            ($this->dashboardFrame)(
                manager: $this->createStub(ManagerInterface::class),
                client: $client,
                serverRequest: $this->createServerRequest(),
                user: $this->createStub(User::class),
                clusterCatalog: $this->clusterCatalog,
                clusterName: 'assets',
                wildcard: 'config.json',
            )
        );
    }

    public function testInvokeRendersAnErrorWhenTheDashboardIsUnreachable(): void
    {
        $this->httpMethodsClient
            ->method('send')
            ->willThrowException(new RuntimeException('unreachable'));

        $errorResponse = $this->createStub(ResponseInterface::class);
        $errorResponse->method('withHeader')->willReturnSelf();
        $errorResponse->method('withBody')->willReturnSelf();
        $this->responseFactory
            ->method('createResponse')
            ->willReturn($errorResponse);

        $this->assertInstanceOf(
            DashboardFrame::class,
            ($this->dashboardFrame)(
                manager: $this->createStub(ManagerInterface::class),
                client: $this->createStub(EastClient::class),
                serverRequest: $this->createServerRequest(),
                user: $this->createAdmin(),
                clusterCatalog: $this->clusterCatalog,
                clusterName: 'clusterName',
                wildcard: '#/workloads',
            )
        );
    }

    public function testInvokeThrowsForNonAdminWithoutWallet(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionCode(403);

        ($this->dashboardFrame)(
            manager: $this->createStub(ManagerInterface::class),
            client: $this->createStub(EastClient::class),
            serverRequest: $this->createServerRequest(),
            user: $this->createStub(User::class),
            clusterCatalog: $this->clusterCatalog,
            clusterName: 'clusterName',
            wildcard: '#/workloads',
            accountWallet: null,
            envName: 'prod',
        );
    }

    public function testInvokeThrowsForNonAdminWithoutEnvName(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionCode(400);

        ($this->dashboardFrame)(
            manager: $this->createStub(ManagerInterface::class),
            client: $this->createStub(EastClient::class),
            serverRequest: $this->createServerRequest(),
            user: $this->createStub(User::class),
            clusterCatalog: $this->clusterCatalog,
            clusterName: 'clusterName',
            wildcard: '#/workloads',
            accountWallet: $this->createWallet(true, true),
            envName: null,
        );
    }

    public function testInvokeThrowsForNonAdminWhenTheClusterIsNotInTheWallet(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionCode(403);

        ($this->dashboardFrame)(
            manager: $this->createStub(ManagerInterface::class),
            client: $this->createStub(EastClient::class),
            serverRequest: $this->createServerRequest(),
            user: $this->createStub(User::class),
            clusterCatalog: $this->clusterCatalog,
            clusterName: 'clusterName',
            wildcard: '#/workloads',
            accountWallet: $this->createWallet(false, false),
            envName: 'prod',
        );
    }

    public function testInvokeThrowsForNonAdminWhenTheEnvironmentIsMissing(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionCode(403);

        ($this->dashboardFrame)(
            manager: $this->createStub(ManagerInterface::class),
            client: $this->createStub(EastClient::class),
            serverRequest: $this->createServerRequest(),
            user: $this->createStub(User::class),
            clusterCatalog: $this->clusterCatalog,
            clusterName: 'clusterName',
            wildcard: '#/workloads',
            accountWallet: $this->createWallet(true, false),
            envName: 'prod',
        );
    }

    public function testInvokeThrowsOnNonKubernetesCluster(): void
    {
        $catalog = new ClusterCatalog(
            ['clusterName' => new DockerComposeCluster(
                name: 'foo',
                sluggyName: 'foo',
                type: 'docker-compose',
                masterAddress: 'ssh://u@h:22',
                dashboardAddress: 'foo',
                isExternal: false,
                clientKey: 'k',
            )],
            [],
        );

        $this->expectException(UnsupportedClusterTypeException::class);

        ($this->dashboardFrame)(
            manager: $this->createStub(ManagerInterface::class),
            client: $this->createStub(EastClient::class),
            serverRequest: $this->createStub(ServerRequestInterface::class),
            user: $this->createStub(User::class),
            clusterCatalog: $catalog,
            clusterName: 'clusterName',
            wildcard: '*',
        );
    }
}
