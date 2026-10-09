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
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\StreamFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
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
use Teknoo\Space\Service\DashboardAvailability;

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
     * @var array{method: string, uri: string, headers: array<string, string>, body: ?string}|null
     */
    private ?array $sentRequest = null;

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
            dashboardAddress: 'https://dashboard.test',
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
            new DashboardAvailability(),
        );
    }

    /**
     * @param array<string, string> $headers
     */
    private function createServerRequest(
        string $method = 'GET',
        string $query = '',
        array $headers = [],
        string $body = '',
    ): ServerRequestInterface {
        return new ServerRequest(
            uri: 'https://space.test/dashboard/frame/cluster-name/prod/?' . $query,
            method: $method,
            body: new StreamFactory()->createStream($body),
            headers: $headers,
        );
    }

    private function createUser(): User&Stub
    {
        $user = $this->createStub(User::class);
        $user->method('getRoles')->willReturn(['ROLE_USER']);

        return $user;
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
    private function prepareDashboardResponse(
        string $expectedUri,
        array $headers = [],
        string $expectedMethod = 'GET',
    ): ResponseInterface&MockObject {
        $finalResponse = $this->createMock(ResponseInterface::class);
        $finalResponse->method('withBody')->willReturnSelf();
        $withHeaderExpectation = $this->never();
        if (!empty($headers)) {
            $withHeaderExpectation = $this->once();
        }

        $finalResponse->expects($withHeaderExpectation)
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
                function (
                    string $method,
                    string $uri,
                    array $headers = [],
                    ?string $body = null,
                ) use (
                    $expectedUri,
                    $expectedMethod,
                    $response,
                ): ResponseInterface {
                    $this->assertSame($expectedMethod, $method);
                    $this->assertSame($expectedUri, $uri);
                    $this->sentRequest = ['method' => $method, 'uri' => $uri, 'headers' => $headers, 'body' => $body];

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
            'https://dashboard.test/#/workloads?namespace=space-ns',
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
        $this->prepareDashboardResponse('https://dashboard.test/#/workloads?namespace=_all');

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
        $this->prepareDashboardResponse('https://dashboard.test/assets/config/config.json');

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
            serverRequest: $this->createServerRequest(),
            user: $this->createStub(User::class),
            clusterCatalog: $catalog,
            clusterName: 'clusterName',
            wildcard: '*',
        );
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function unavailableDashboardsProvider(): iterable
    {
        yield 'no dashboard' => ['', false];
        yield 'external cluster' => ['https://dashboard.client.test/', true];
    }

    #[DataProvider('unavailableDashboardsProvider')]
    public function testInvokeRefusesAClusterWithoutAvailableDashboard(string $address, bool $isExternal): void
    {
        $catalog = new ClusterCatalog(
            ['clusterName' => new ClusterConfig(
                name: 'foo',
                sluggyName: 'foo',
                type: 'kubernetes',
                masterAddress: 'https://kubernetes.client.test',
                storageProvisioner: 'foo',
                dashboardAddress: $address,
                kubernetesClient: $this->createStub(Client::class),
                token: 'foo',
                supportRegistry: false,
                useHnc: false,
                isExternal: $isExternal,
            )],
            [],
        );

        $httpMethodsClient = $this->createMock(HttpMethodsClientInterface::class);
        $httpMethodsClient->expects($this->never())->method('send');

        $dashboardFrame = new DashboardFrame(
            $httpMethodsClient,
            $this->responseFactory,
            $this->streamFactory,
            $this->urlGenerator,
            $this->template,
            new DashboardAvailability(),
        );

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionCode(404);

        $dashboardFrame(
            manager: $this->createStub(ManagerInterface::class),
            client: $this->createStub(EastClient::class),
            serverRequest: $this->createServerRequest(),
            user: $this->createAdmin(),
            clusterCatalog: $catalog,
            clusterName: 'clusterName',
            wildcard: '',
        );
    }

    public function testInvokeForwardsTheQueryStringAndTheAcceptHeader(): void
    {
        $this->prepareDashboardResponse(
            'https://dashboard.test/api/v1/namespaces/space-ns/pods?limit=10&labelSelector=app%3Dfoo',
        );

        ($this->dashboardFrame)(
            manager: $this->createStub(ManagerInterface::class),
            client: $this->createStub(EastClient::class),
            serverRequest: $this->createServerRequest(
                query: 'limit=10&labelSelector=app%3Dfoo',
                headers: ['Accept' => 'application/json', 'Cookie' => 'PHPSESSID=secret'],
            ),
            user: $this->createAdmin(),
            clusterCatalog: $this->clusterCatalog,
            clusterName: 'clusterName',
            wildcard: 'api/v1/namespaces/space-ns/pods',
        );

        $this->assertSame(
            ['accept' => 'application/json', 'Authorization' => 'Bearer foo'],
            $this->sentRequest['headers'] ?? null,
        );
        $this->assertNull($this->sentRequest['body'] ?? null);
    }

    public function testInvokeDoesNotAppendTheQueryStringToAnAnchoredWildcard(): void
    {
        $this->prepareDashboardResponse('https://dashboard.test/#/workloads?namespace=_all');

        ($this->dashboardFrame)(
            manager: $this->createStub(ManagerInterface::class),
            client: $this->createStub(EastClient::class),
            serverRequest: $this->createServerRequest(query: 'foo=bar'),
            user: $this->createAdmin(),
            clusterCatalog: $this->clusterCatalog,
            clusterName: 'clusterName',
            wildcard: '#/workloads',
        );

        $this->assertSame('https://dashboard.test/#/workloads?namespace=_all', $this->sentRequest['uri'] ?? null);
    }

    public function testInvokeForwardsTheBodyOfASameOriginMutatingRequest(): void
    {
        $this->prepareDashboardResponse(
            expectedUri: 'https://dashboard.test/apis/authorization.k8s.io/v1/selfsubjectrulesreviews',
            expectedMethod: 'POST',
        );

        ($this->dashboardFrame)(
            manager: $this->createStub(ManagerInterface::class),
            client: $this->createStub(EastClient::class),
            serverRequest: $this->createServerRequest(
                method: 'POST',
                headers: ['Sec-Fetch-Site' => 'same-origin', 'Content-Type' => 'application/json'],
                body: '{"spec":{"namespace":"space-ns"}}',
            ),
            user: $this->createUser(),
            clusterCatalog: $this->clusterCatalog,
            clusterName: 'clusterName',
            wildcard: 'apis/authorization.k8s.io/v1/selfsubjectrulesreviews',
            accountWallet: $this->createWallet(true, true),
            envName: 'prod',
        );

        $this->assertSame(
            ['content-type' => 'application/json', 'Authorization' => 'Bearer env-token'],
            $this->sentRequest['headers'] ?? null,
        );
        $this->assertSame('{"spec":{"namespace":"space-ns"}}', $this->sentRequest['body'] ?? null);
    }

    public function testInvokeForwardsAMutatingRequestFromTheSameHostWithoutFetchMetadata(): void
    {
        $this->prepareDashboardResponse(
            expectedUri: 'https://dashboard.test/api/v1/namespaces/space-ns/pods/foo',
            expectedMethod: 'PATCH',
        );

        ($this->dashboardFrame)(
            manager: $this->createStub(ManagerInterface::class),
            client: $this->createStub(EastClient::class),
            serverRequest: $this->createServerRequest(
                method: 'PATCH',
                headers: ['Origin' => 'https://SPACE.test', 'Content-Type' => 'application/merge-patch+json'],
                body: '{}',
            ),
            user: $this->createAdmin(),
            clusterCatalog: $this->clusterCatalog,
            clusterName: 'clusterName',
            wildcard: 'api/v1/namespaces/space-ns/pods/foo',
        );

        $this->assertSame('{}', $this->sentRequest['body'] ?? null);
    }

    public function testInvokeKeepsTheHostOfTheDashboardWhateverThePath(): void
    {
        $this->prepareDashboardResponse('https://dashboard.test/@evil.test/steal');

        ($this->dashboardFrame)(
            manager: $this->createStub(ManagerInterface::class),
            client: $this->createStub(EastClient::class),
            serverRequest: $this->createServerRequest(),
            user: $this->createAdmin(),
            clusterCatalog: $this->clusterCatalog,
            clusterName: 'clusterName',
            wildcard: '//@evil.test/steal',
        );

        $this->assertSame('https://dashboard.test/@evil.test/steal', $this->sentRequest['uri'] ?? null);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function traversalWildcardsProvider(): iterable
    {
        yield 'parent' => ['..'];
        yield 'leading parent' => ['../admin'];
        yield 'inner parent' => ['api/../../admin'];
        yield 'trailing parent' => ['api/..'];
    }

    #[DataProvider('traversalWildcardsProvider')]
    public function testInvokeRefusesAPathGoingUpFromTheDashboardAddress(string $wildcard): void
    {
        $httpMethodsClient = $this->createMock(HttpMethodsClientInterface::class);
        $httpMethodsClient->expects($this->never())->method('send');

        $dashboardFrame = new DashboardFrame(
            $httpMethodsClient,
            $this->responseFactory,
            $this->streamFactory,
            $this->urlGenerator,
            $this->template,
            new DashboardAvailability(),
        );

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionCode(400);

        $dashboardFrame(
            manager: $this->createStub(ManagerInterface::class),
            client: $this->createStub(EastClient::class),
            serverRequest: $this->createServerRequest(),
            user: $this->createAdmin(),
            clusterCatalog: $this->clusterCatalog,
            clusterName: 'clusterName',
            wildcard: $wildcard,
        );
    }

    /**
     * @return iterable<string, array{array<string, string>}>
     */
    public static function crossOriginHeadersProvider(): iterable
    {
        yield 'cross-site fetch' => [['Sec-Fetch-Site' => 'cross-site', 'Origin' => 'https://space.test']];
        yield 'same-site fetch' => [['Sec-Fetch-Site' => 'same-site']];
        yield 'foreign origin' => [['Origin' => 'https://evil.test']];
        yield 'no origin' => [[]];
    }

    /**
     * @param array<string, string> $headers
     */
    #[DataProvider('crossOriginHeadersProvider')]
    public function testInvokeRefusesAMutatingRequestNotIssuedBySpace(array $headers): void
    {
        $httpMethodsClient = $this->createMock(HttpMethodsClientInterface::class);
        $httpMethodsClient->expects($this->never())->method('send');

        $dashboardFrame = new DashboardFrame(
            $httpMethodsClient,
            $this->responseFactory,
            $this->streamFactory,
            $this->urlGenerator,
            $this->template,
            new DashboardAvailability(),
        );

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionCode(403);

        $dashboardFrame(
            manager: $this->createStub(ManagerInterface::class),
            client: $this->createStub(EastClient::class),
            serverRequest: $this->createServerRequest(method: 'DELETE', headers: $headers),
            user: $this->createAdmin(),
            clusterCatalog: $this->clusterCatalog,
            clusterName: 'clusterName',
            wildcard: 'api/v1/namespaces/space-ns/pods/foo',
        );
    }
}
