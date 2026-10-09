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
use Closure;
use Http\Client\Common\HttpMethodsClientInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\StreamFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teknoo\East\Foundation\Client\ClientInterface as EastClient;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Foundation\Template\ResultInterface;
use Teknoo\Kubernetes\Client;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Misc\DashboardFrame;
use Teknoo\Space\Object\Config\DashboardProfile;
use Teknoo\Space\Object\Config\KubernetesCluster;
use Teknoo\Space\Object\DTO\DashboardTarget;

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
    private const string HEADLAMP_PAGE = '<!doctype html><html><head><meta charset="utf-8">'
        . '<script>window.headlampBaseUrl = \'/__headlamp\';</script><link href="/__headlamp/favicon.ico">'
        . '<style>a{background:url(/__headlamp/bg.png)}</style></head><body><img src="/__headlamp-logo.svg">'
        . '<script src="/__headlamp/assets/index.js"></script></body></html>';

    private HttpMethodsClientInterface&Stub $httpMethodsClient;

    private DashboardFrame $dashboardFrame;

    /**
     * @var array{method: string, uri: string, headers: array<string, string>, body: ?string}|null
     */
    private ?array $sentRequest = null;

    private ?ResponseInterface $acceptedResponse = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpMethodsClient = $this->createStub(HttpMethodsClientInterface::class);
        $this->dashboardFrame = $this->createDashboardFrame($this->httpMethodsClient);
    }

    /**
     * @param (Closure(string): array<string>)|null $hostResolver
     */
    private function createDashboardFrame(
        HttpMethodsClientInterface $httpMethodsClient,
        ?Closure $hostResolver = null,
    ): DashboardFrame {
        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->willReturnCallback(
                fn (string $name, array $parameters, int $type): string => 'https://space.test/dashboard/frame/'
                    . $parameters['clusterName'] . '/' . $parameters['envName'] . '/'
            );

        $result = $this->createStub(ResultInterface::class);
        $result->method('__toString')->willReturn('The dashboard is unreachable');

        $engine = $this->createStub(EngineInterface::class);
        $engine->method('render')
            ->willReturnCallback(
                static function (PromiseInterface $promise) use ($engine, $result): EngineInterface {
                    $promise->success($result);

                    return $engine;
                }
            );

        return new DashboardFrame(
            $httpMethodsClient,
            new ResponseFactory(),
            new StreamFactory(),
            $urlGenerator,
            $engine,
            $hostResolver,
        );
    }

    private function headlampProfile(): DashboardProfile
    {
        return new DashboardProfile(
            name: 'headlamp',
            requestHeaders: ['Authorization' => 'Bearer {token}'],
            headSnippet: '<script>all();</script>',
            namespacedHeadSnippet: '<script>ns(\'{namespace}\');</script>',
            rewriteBasePath: true,
        );
    }

    private function legacyProfile(): DashboardProfile
    {
        return new DashboardProfile(
            name: 'kubernetes-dashboard',
            requestHeaders: ['Authorization' => 'Bearer {token}'],
            headSnippet: '<base href="{baseHref}">',
            namespacedHeadSnippet: '<base href="{baseHref}">',
            pathAliases: ['config/config.json' => 'assets/config/config.json'],
        );
    }

    private function createTarget(
        ?DashboardProfile $profile = null,
        ?string $namespace = 'space-ns',
        string $dashboardAddress = 'http://headlamp.test/__headlamp/',
        string $envName = 'prod',
        bool $isExternal = false,
    ): DashboardTarget {
        return new DashboardTarget(
            cluster: new KubernetesCluster(
                name: 'Cluster Name',
                sluggyName: 'cluster-name',
                type: 'kubernetes',
                masterAddress: 'https://kubernetes.test',
                storageProvisioner: 'foo',
                dashboardAddress: $dashboardAddress,
                kubernetesClient: $this->createStub(Client::class),
                token: 'cluster-token',
                supportRegistry: false,
                useHnc: false,
                isExternal: $isExternal,
            ),
            profile: $profile ?? $this->headlampProfile(),
            token: null === $namespace ? 'cluster-token' : 'env-token',
            clusterName: 'cluster-name',
            envName: $envName,
            namespace: $namespace,
        );
    }

    /**
     * @param array<string, string> $headers
     */
    private function createServerRequest(
        string $path = '',
        string $method = 'GET',
        string $query = '',
        array $headers = [],
        string $body = '',
        string $envName = 'prod',
    ): ServerRequestInterface {
        return new ServerRequest(
            uri: 'https://space.test/dashboard/frame/cluster-name/' . $envName . '/' . $path
                . ('' !== $query ? '?' . $query : ''),
            method: $method,
            body: new StreamFactory()->createStream($body),
            headers: $headers,
        );
    }

    /**
     * @param array<string, string|string[]> $headers
     */
    private function dashboardAnswers(
        string $body = self::HEADLAMP_PAGE,
        array $headers = ['content-type' => 'text/html; charset=utf-8'],
        int $status = 200,
    ): void {
        $this->httpMethodsClient
            ->method('send')
            ->willReturnCallback(
                function (
                    string $method,
                    string $uri,
                    array $sentHeaders = [],
                    ?string $sentBody = null,
                ) use (
                    $body,
                    $headers,
                    $status,
                ): ResponseInterface {
                    $this->sentRequest = [
                        'method' => $method,
                        'uri' => $uri,
                        'headers' => $sentHeaders,
                        'body' => $sentBody,
                    ];

                    return new Response(new StreamFactory()->createStream($body), $status, $headers);
                }
            );
    }

    private function relay(
        ServerRequestInterface $serverRequest,
        ?DashboardTarget $target = null,
        ?DashboardFrame $dashboardFrame = null,
    ): void {
        $client = $this->createStub(EastClient::class);
        $client->method('acceptResponse')
            ->willReturnCallback(function (ResponseInterface $response) use ($client): EastClient {
                $this->acceptedResponse = $response;

                return $client;
            });

        $dashboardFrame ??= $this->dashboardFrame;

        $this->assertInstanceOf(
            DashboardFrame::class,
            $dashboardFrame(
                manager: $this->createStub(ManagerInterface::class),
                client: $client,
                serverRequest: $serverRequest,
                dashboardTarget: $target ?? $this->createTarget(),
            ),
        );
    }

    private function createNeverCalledDashboardFrame(): DashboardFrame
    {
        $httpMethodsClient = $this->createMock(HttpMethodsClientInterface::class);
        $httpMethodsClient->expects($this->never())->method('send');

        return $this->createDashboardFrame($httpMethodsClient);
    }

    public function testRelayTheHeadlampEntryForAUser(): void
    {
        $this->dashboardAnswers();

        $this->relay($this->createServerRequest());

        $this->assertSame(
            [
                'method' => 'GET',
                'uri' => 'http://headlamp.test/__headlamp/',
                'headers' => ['Accept-Encoding' => 'identity', 'Authorization' => 'Bearer env-token'],
                'body' => null,
            ],
            $this->sentRequest,
        );

        $this->assertSame(200, $this->acceptedResponse?->getStatusCode());
        $this->assertSame('text/html; charset=utf-8', $this->acceptedResponse->getHeaderLine('content-type'));
        $this->assertSame(
            '<!doctype html><html><head><script>ns(\'space-ns\');</script><meta charset="utf-8">'
            . '<script>window.headlampBaseUrl = \'/dashboard/frame/cluster-name/prod\';</script>'
            . '<link href="/dashboard/frame/cluster-name/prod/favicon.ico">'
            . '<style>a{background:url(/dashboard/frame/cluster-name/prod/bg.png)}</style></head><body>'
            . '<img src="/__headlamp-logo.svg"><script src="/dashboard/frame/cluster-name/prod/assets/index.js">'
            . '</script></body></html>',
            (string) $this->acceptedResponse->getBody(),
        );
    }

    public function testRelayADeepLinkOfHeadlampForAnAdministrator(): void
    {
        $this->dashboardAnswers();

        $this->relay(
            $this->createServerRequest(path: 'c/main/workloads', query: 'namespace=space-ns', envName: '_all'),
            $this->createTarget(namespace: null, envName: '_all'),
        );

        $this->assertSame(
            'http://headlamp.test/__headlamp/c/main/workloads?namespace=space-ns',
            $this->sentRequest['uri'] ?? null,
        );
        $this->assertSame('Bearer cluster-token', $this->sentRequest['headers']['Authorization'] ?? null);
        $this->assertStringStartsWith(
            '<!doctype html><html><head><script>all();</script><meta charset="utf-8">',
            (string) $this->acceptedResponse?->getBody(),
        );
    }

    public function testRelayTheLegacyDashboardForAnAdministrator(): void
    {
        $this->dashboardAnswers('<html lang="en"><head lang="en"><title>Dashboard</title></head></html>');

        $this->relay(
            $this->createServerRequest(path: 'config/config.json', envName: '_all'),
            $this->createTarget(
                profile: $this->legacyProfile(),
                namespace: null,
                dashboardAddress: 'https://dashboard.test',
                envName: '_all',
            ),
        );

        $this->assertSame('https://dashboard.test/assets/config/config.json', $this->sentRequest['uri'] ?? null);
        $this->assertSame(
            '<html lang="en"><head lang="en"><base href="https://space.test/dashboard/frame/cluster-name/_all/">'
            . '<title>Dashboard</title></head></html>',
            (string) $this->acceptedResponse?->getBody(),
        );
    }

    public function testRelayAPageAsIsWhenTheProfileDoesNotAdaptIt(): void
    {
        $this->dashboardAnswers();

        $this->relay(
            $this->createServerRequest(),
            $this->createTarget(profile: new DashboardProfile(name: 'raw', requestHeaders: [])),
        );

        $this->assertSame(['Accept-Encoding' => 'identity'], $this->sentRequest['headers'] ?? null);
        $this->assertSame(self::HEADLAMP_PAGE, (string) $this->acceptedResponse?->getBody());
    }

    public function testRelayANonHtmlResponseAsIsWithItsStatusAndOnlyTheAllowedHeaders(): void
    {
        $this->dashboardAnswers(
            body: '{"kind":"Status","message":"/__headlamp/ not found"}',
            headers: [
                'content-type' => 'application/json',
                'cache-control' => 'no-cache',
                'set-cookie' => 'headlamp-auth-main.0=foo',
                'x-frame-options' => 'DENY',
                'content-security-policy' => "frame-ancestors 'none'",
            ],
            status: 404,
        );

        $this->relay($this->createServerRequest(path: 'clusters/main/api/v1/namespaces/space-ns/pods/foo'));

        $this->assertSame(404, $this->acceptedResponse?->getStatusCode());
        $this->assertSame(
            ['content-type' => ['application/json'], 'cache-control' => ['no-cache']],
            $this->acceptedResponse->getHeaders(),
        );
        $this->assertSame(
            '{"kind":"Status","message":"/__headlamp/ not found"}',
            (string) $this->acceptedResponse->getBody(),
        );
    }

    public function testRelayTheQueryStringAndTheBodyOfASameOriginMutatingRequest(): void
    {
        $this->dashboardAnswers(body: '{}', headers: ['content-type' => 'application/json']);

        $this->relay(
            $this->createServerRequest(
                path: 'clusters/main/apis/authorization.k8s.io/v1/selfsubjectrulesreviews',
                method: 'POST',
                query: 'dryRun=All',
                headers: [
                    'Sec-Fetch-Site' => 'same-origin',
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Cookie' => 'PHPSESSID=secret',
                    'Authorization' => 'Bearer a-token-of-the-browser',
                ],
                body: '{"spec":{"namespace":"space-ns"}}',
            ),
        );

        $this->assertSame(
            [
                'method' => 'POST',
                'uri' => 'http://headlamp.test/__headlamp/clusters/main/apis/authorization.k8s.io/v1/'
                    . 'selfsubjectrulesreviews?dryRun=All',
                'headers' => [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'Accept-Encoding' => 'identity',
                    'Authorization' => 'Bearer env-token',
                ],
                'body' => '{"spec":{"namespace":"space-ns"}}',
            ],
            $this->sentRequest,
        );
    }

    public function testRelayAMutatingRequestFromTheSameHostWithoutFetchMetadata(): void
    {
        $this->dashboardAnswers(body: '{}', headers: ['content-type' => 'application/json']);

        $this->relay(
            $this->createServerRequest(
                path: 'clusters/main/api/v1/namespaces/space-ns/pods/foo',
                method: 'PATCH',
                headers: ['Origin' => 'https://SPACE.test', 'Content-Type' => 'application/merge-patch+json'],
                body: '{}',
            ),
        );

        $this->assertSame('PATCH', $this->sentRequest['method'] ?? null);
        $this->assertSame('{}', $this->sentRequest['body'] ?? null);
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
    public function testRefuseAMutatingRequestNotIssuedBySpace(array $headers): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionCode(403);

        $this->relay(
            serverRequest: $this->createServerRequest(
                path: 'clusters/main/api/v1/namespaces/space-ns/pods/foo',
                method: 'DELETE',
                headers: $headers,
            ),
            dashboardFrame: $this->createNeverCalledDashboardFrame(),
        );
    }

    /**
     * @return iterable<string, array{string, array<string, string>}>
     */
    public static function streamedRequestsProvider(): iterable
    {
        yield 'websocket' => ['', ['Upgrade' => 'websocket', 'Connection' => 'Upgrade']];
        yield 'watch' => ['watch=1&resourceVersion=42', []];
        yield 'watch true' => ['watch=TRUE', []];
        yield 'follow logs' => ['container=app&follow=true', []];
    }

    /**
     * @param array<string, string> $headers
     */
    #[DataProvider('streamedRequestsProvider')]
    public function testRefuseAStreamedRequestAtOnce(string $query, array $headers): void
    {
        $this->relay(
            serverRequest: $this->createServerRequest(
                path: 'clusters/main/api/v1/namespaces/space-ns/pods',
                query: $query,
                headers: $headers,
            ),
            dashboardFrame: $this->createNeverCalledDashboardFrame(),
        );

        $this->assertSame(501, $this->acceptedResponse?->getStatusCode());
        $this->assertSame(
            'Streamed requests are not relayed to the dashboard',
            (string) $this->acceptedResponse->getBody(),
        );
    }

    public function testRelayARequestNotAskingForAStream(): void
    {
        $this->dashboardAnswers(body: '{}', headers: ['content-type' => 'application/json']);

        $this->relay(
            $this->createServerRequest(
                path: 'clusters/main/api/v1/namespaces/space-ns/pods/foo/log',
                query: 'watch=0&follow=false&labelSelector[]=foo',
            ),
        );

        $this->assertSame(200, $this->acceptedResponse?->getStatusCode());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function traversalPathsProvider(): iterable
    {
        yield 'parent' => ['..'];
        yield 'leading parent' => ['../admin'];
        yield 'inner parent' => ['api/../../admin'];
        yield 'encoded parent' => ['%2e%2E/admin'];
    }

    #[DataProvider('traversalPathsProvider')]
    public function testRefuseAPathGoingUpFromTheDashboardAddress(string $path): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionCode(400);

        $this->relay(
            serverRequest: $this->createServerRequest(path: $path),
            dashboardFrame: $this->createNeverCalledDashboardFrame(),
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function foreignHostPathsProvider(): iterable
    {
        yield 'user info' => ['@evil.test/steal'];
        yield 'network path' => ['/@evil.test/steal'];
    }

    #[DataProvider('foreignHostPathsProvider')]
    public function testKeepTheHostOfTheDashboardWhateverThePath(string $path): void
    {
        $this->dashboardAnswers();

        $this->relay(
            $this->createServerRequest(path: $path),
            $this->createTarget(dashboardAddress: 'http://headlamp.test'),
        );

        $this->assertSame('http://headlamp.test/@evil.test/steal', $this->sentRequest['uri'] ?? null);
    }

    public function testRefuseARequestOutsideTheFrame(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionCode(400);

        $this->relay(
            serverRequest: new ServerRequest(uri: 'https://space.test/dashboard/frame/other-cluster/prod/'),
            dashboardFrame: $this->createNeverCalledDashboardFrame(),
        );
    }

    public function testRelayTheDashboardOfAnExternalClusterServedByAPublicHost(): void
    {
        $this->dashboardAnswers();

        $resolvedHosts = [];
        $dashboardFrame = $this->createDashboardFrame(
            $this->httpMethodsClient,
            static function (string $host) use (&$resolvedHosts): array {
                $resolvedHosts[] = $host;

                return ['93.184.215.14', '2606:2800:21f:cb07:6820:80da:af6b:8b2c'];
            },
        );

        $this->relay(
            serverRequest: $this->createServerRequest(),
            target: $this->createTarget(dashboardAddress: 'https://headlamp.client.test/__headlamp/', isExternal: true),
            dashboardFrame: $dashboardFrame,
        );

        $this->assertSame(['headlamp.client.test'], $resolvedHosts);
        $this->assertSame('https://headlamp.client.test/__headlamp/', $this->sentRequest['uri'] ?? null);
    }

    /**
     * @return iterable<string, array{string, array<string>}>
     */
    public static function nonPublicExternalDashboardsProvider(): iterable
    {
        yield 'http' => ['http://headlamp.client.test/__headlamp/', ['93.184.215.14']];
        yield 'unresolved' => ['https://headlamp.client.test/__headlamp/', []];
        yield 'private network' => ['https://headlamp.client.test/__headlamp/', ['10.1.2.3']];
        yield 'cloud metadata' => ['https://headlamp.client.test/__headlamp/', ['169.254.169.254']];
        yield 'one private address' => ['https://headlamp.client.test/__headlamp/', ['93.184.215.14', 'fd00::1']];
    }

    /**
     * @param array<string> $addresses
     */
    #[DataProvider('nonPublicExternalDashboardsProvider')]
    public function testRefuseTheDashboardOfAnExternalClusterNotServedByAPublicHost(
        string $dashboardAddress,
        array $addresses,
    ): void {
        $httpMethodsClient = $this->createMock(HttpMethodsClientInterface::class);
        $httpMethodsClient->expects($this->never())->method('send');

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionCode(403);

        $this->relay(
            serverRequest: $this->createServerRequest(),
            target: $this->createTarget(dashboardAddress: $dashboardAddress, isExternal: true),
            dashboardFrame: $this->createDashboardFrame($httpMethodsClient, static fn (): array => $addresses),
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function localHostsProvider(): iterable
    {
        yield 'ipv4 loopback' => ['https://127.0.0.1/__headlamp/'];
        yield 'ipv6 loopback' => ['https://[::1]/__headlamp/'];
        yield 'localhost' => ['https://localhost/__headlamp/'];
    }

    #[DataProvider('localHostsProvider')]
    public function testTheDnsResolverRefusesLocalHosts(string $dashboardAddress): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionCode(403);

        $this->relay(
            serverRequest: $this->createServerRequest(),
            target: $this->createTarget(dashboardAddress: $dashboardAddress, isExternal: true),
            dashboardFrame: $this->createNeverCalledDashboardFrame(),
        );
    }

    public function testTheDnsResolverAcceptsAPublicAddress(): void
    {
        $this->dashboardAnswers();

        $this->relay(
            $this->createServerRequest(),
            $this->createTarget(dashboardAddress: 'https://93.184.215.14/__headlamp/', isExternal: true),
        );

        $this->assertSame('https://93.184.215.14/__headlamp/', $this->sentRequest['uri'] ?? null);
    }

    public function testRenderAnErrorWhenTheDashboardIsUnreachable(): void
    {
        $this->httpMethodsClient
            ->method('send')
            ->willThrowException(new RuntimeException('unreachable'));

        $this->relay($this->createServerRequest());

        $this->assertSame(502, $this->acceptedResponse?->getStatusCode());
        $this->assertSame('The dashboard is unreachable', (string) $this->acceptedResponse->getBody());
    }
}
