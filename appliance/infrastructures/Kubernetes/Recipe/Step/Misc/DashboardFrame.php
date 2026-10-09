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

namespace Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Misc;

use BadMethodCallException;
use Http\Client\Common\HttpMethodsClientInterface;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Recipe\Step\Traits\TemplateTrait;
use Teknoo\East\Foundation\Client\ClientInterface as EastClient;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\DashboardFrameInterface;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\Exception\UnsupportedClusterTypeException;
use Teknoo\Space\Object\Config\KubernetesCluster;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Throwable;

use function http_build_query;
use function in_array;
use function is_string;
use function parse_url;
use function preg_replace;
use function str_contains;
use function strcasecmp;
use function strtolower;
use function strtoupper;

use const PHP_URL_HOST;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class DashboardFrame implements DashboardFrameInterface
{
    use TemplateTrait;

    /**
     * Methods without side effect: the only ones relayed without proving the request comes from Space itself.
     */
    private const array SAFE_METHODS = ['GET', 'HEAD'];

    /**
     * Request headers of the browser relayed to the dashboard. Cookies and credentials of Space are never relayed.
     */
    private const array FORWARDED_REQUEST_HEADERS = ['accept', 'content-type'];

    public function __construct(
        private readonly HttpMethodsClientInterface $httpMethodsClient,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
        EngineInterface $templating,
    ) {
        $this->templating = $templating;
        $this->streamFactory = $streamFactory;
        $this->responseFactory = $responseFactory;
    }

    private function getDashboardUrl(KubernetesCluster $cluster, ?AccountEnvironment $env, string $wildcard): string
    {
        if (!str_contains($wildcard, '#')) {
            if ('config/config.json' === $wildcard) {
                $wildcard = 'assets/' . $wildcard;
            }

            return $cluster->dashboardAddress . $wildcard;
        }

        $url = $cluster->dashboardAddress . $wildcard;

        if (null !== $env) {
            $url .= '?' . http_build_query(['namespace' => $env->getNamespace()]);
        } else {
            $url .= '?' . http_build_query(['namespace' => '_all']);
        }

        return $url;
    }

    private function isSafeMethod(ServerRequestInterface $serverRequest): bool
    {
        return in_array(strtoupper($serverRequest->getMethod()), self::SAFE_METHODS, true);
    }

    /**
     * The relay acts with the credentials of the environment, so a request with side effects is relayed only when
     * it is issued by a page of Space itself, never by a third-party site abusing the user's session (CSRF).
     * Browsers send `Sec-Fetch-Site`; older ones are checked against their `Origin`.
     */
    private function assertSameOrigin(ServerRequestInterface $serverRequest): void
    {
        if ($this->isSafeMethod($serverRequest)) {
            return;
        }

        $fetchSite = strtolower($serverRequest->getHeaderLine('sec-fetch-site'));
        if ('same-origin' === $fetchSite) {
            return;
        }

        if ('' === $fetchSite) {
            $originHost = parse_url($serverRequest->getHeaderLine('origin'), PHP_URL_HOST);
            if (
                is_string($originHost)
                && 0 === strcasecmp($originHost, $serverRequest->getUri()->getHost())
            ) {
                return;
            }
        }

        throw new BadMethodCallException(
            message: "Only requests issued by Space are relayed to the dashboard",
            code: 403,
        );
    }

    /**
     * @return array<string, string>
     */
    private function getForwardedHeaders(ServerRequestInterface $serverRequest): array
    {
        $headers = [];
        foreach (self::FORWARDED_REQUEST_HEADERS as $headerName) {
            $value = $serverRequest->getHeaderLine($headerName);
            if ('' !== $value) {
                $headers[$headerName] = $value;
            }
        }

        return $headers;
    }

    public function __invoke(
        ManagerInterface $manager,
        EastClient $client,
        ServerRequestInterface $serverRequest,
        User $user,
        ClusterCatalog $clusterCatalog,
        string $clusterName,
        string $wildcard = '',
        ?Account $account = null,
        ?AccountWallet $accountWallet = null,
        ?string $envName = null,
    ): DashboardFrameInterface {
        $this->assertSameOrigin($serverRequest);

        if (empty($wildcard)) {
            $wildcard = '#/workloads';
        }

        //Hack because last version of dashboard does not respect base uri
        if ('assets' === $clusterName && 'config.json' === $wildcard) {
            $client->acceptResponse(new Response($this->streamFactory->createStream('Not found'), 404));

            return $this;
        }

        $clusterConfig = $clusterCatalog->getCluster($clusterName);
        if (!$clusterConfig instanceof KubernetesCluster) {
            throw new UnsupportedClusterTypeException('This step only supports Kubernetes clusters');
        }

        $isAdmin = in_array('ROLE_ADMIN', (array) $user->getRoles());
        $accountEnvironment = null;

        if (!$isAdmin) {
            if (null === $accountWallet) {
                throw new BadMethodCallException(message: "Wallet is mandatory for non admin user", code: 403);
            }

            if (null === $envName) {
                throw new BadMethodCallException(message: "Environment name is mandatory", code: 400);
            }

            if (!$accountWallet->has($clusterConfig->name, $envName)) {
                throw new BadMethodCallException(message: "Cluster is not allowed for this user", code: 403);
            }

            $accountEnvironment = $accountWallet->get($clusterConfig->name, $envName);

            if (null === $accountEnvironment) {
                throw new BadMethodCallException(message: "Account environment missing", code: 403);
            }
        }

        $dashboardUrl = $this->getDashboardUrl($clusterConfig, $accountEnvironment, $wildcard);

        //A fragment is resolved by the browser, the query string of the page belongs to the dashboard's request
        $query = $serverRequest->getUri()->getQuery();
        if ('' !== $query && !str_contains($dashboardUrl, '#')) {
            $dashboardUrl .= '?' . $query;
        }

        try {
            $responseDashboard = $this->httpMethodsClient->send(
                method: $serverRequest->getMethod(),
                uri: $dashboardUrl,
                headers: [
                    ...$this->getForwardedHeaders($serverRequest),
                    'Authorization' => 'Bearer ' . trim($accountEnvironment?->getToken() ?? $clusterConfig->token),
                ],
                body: $this->isSafeMethod($serverRequest) ? null : (string) $serverRequest->getBody(),
            );
        } catch (Throwable $error) {
            $this->render(
                client: $client,
                view: '@TeknooSpace/Dashboard/server-error.html.twig',
                parameters: ['error' => $error],
                status: 502,
            );

            return $this;
        }

        $response = $this->responseFactory->createResponse(
            $responseDashboard->getStatusCode(),
            $responseDashboard->getReasonPhrase(),
        );

        $headersList = [
            'content-type',
            'accept-ranges',
            'cache-control',
            'last-modified',
            'strict-transport-security',
        ];

        foreach ($headersList as $headerName) {
            $headersValues = $responseDashboard->getHeader($headerName);
            if (empty($headersValues)) {
                continue;
            }

            $response = $response->withHeader(
                $headerName,
                $headersValues,
            );
        }

        $body = (string) $responseDashboard->getBody();

        $baseTag = '<base href="' . $this->urlGenerator->generate(
            'space_dashboard_frame',
            [
                'clusterName' => $clusterName,
                'envName' => $envName,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        ) . '">';

        $body = preg_replace(
            '/<html([^>]*)>.*?<head>/is',
            '<html$1><head>' . $baseTag,
            $body,
        ) ?? $body;

        $response = $response->withBody(
            $this->streamFactory->createStream((string) $body),
        );

        $client->acceptResponse($response);

        return $this;
    }
}
