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
use Closure;
use Http\Client\Common\HttpMethodsClientInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teknoo\East\Common\Recipe\Step\Traits\TemplateTrait;
use Teknoo\East\Foundation\Client\ClientInterface as EastClient;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\DashboardFrameInterface;
use Teknoo\Space\Object\DTO\DashboardTarget;
use Throwable;

use function array_column;
use function array_filter;
use function dns_get_record;
use function filter_var;
use function gethostbynamel;
use function in_array;
use function is_string;
use function ltrim;
use function parse_str;
use function parse_url;
use function preg_match;
use function preg_quote;
use function preg_replace;
use function preg_replace_callback;
use function rawurldecode;
use function rtrim;
use function str_contains;
use function str_starts_with;
use function strcasecmp;
use function strlen;
use function strtolower;
use function strtoupper;
use function substr;
use function trim;

use const DNS_AAAA;
use const FILTER_FLAG_GLOBAL_RANGE;
use const FILTER_VALIDATE_IP;
use const PHP_URL_HOST;
use const PHP_URL_PATH;
use const PHP_URL_SCHEME;

/**
 * Relays the requests of the dashboard frame to the web dashboard of the cluster resolved in the `DashboardTarget`,
 * with the credential of the user injected by the headers of the dashboard's profile, so the user never signs in
 * on the dashboard nor knows the credential. The relay is generic, all differences between dashboards are
 * described by their `DashboardProfile`:
 * - the path requested under the frame's URL (as sent by the browser, with its query string) is requested under
 *   the dashboard's address, which can not be left;
 * - the body and the `Accept` / `Content-Type` headers are relayed, never the cookies nor the credentials of Space;
 * - HTML pages are adapted to be served under the frame's URL (base path, head snippet of the profile).
 *
 * This relay works request by request: websockets and streamed watches, used by dashboards for live updates, logs
 * and terminals, are refused at once (501) instead of holding a PHP worker.
 *
 * The dashboard of a cluster registered by a client is relayed only over https to a public host (see
 * `assertPublicDashboard()`), its address being supplied by the client.
 *
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

    /**
     * Response headers of the dashboard relayed to the browser. Cookies or framing policies of the dashboard are not.
     */
    private const array FORWARDED_RESPONSE_HEADERS = [
        'content-type',
        'accept-ranges',
        'cache-control',
        'last-modified',
        'strict-transport-security',
    ];

    /**
     * Query parameters of the Kubernetes API asking for a response streamed without end.
     */
    private const array STREAMING_PARAMETERS = ['watch', 'follow'];

    /**
     * @var Closure(string): array<string>
     */
    private readonly Closure $hostResolver;

    /**
     * @param (Closure(string): array<string>)|null $hostResolver returns the IP addresses of a host, the DNS by
     *  default
     */
    public function __construct(
        private readonly HttpMethodsClientInterface $httpMethodsClient,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
        EngineInterface $templating,
        ?Closure $hostResolver = null,
    ) {
        $this->templating = $templating;
        $this->streamFactory = $streamFactory;
        $this->responseFactory = $responseFactory;
        $this->hostResolver = $hostResolver ?? self::resolveHost(...);
    }

    /**
     * @return array<string>
     */
    private static function resolveHost(string $host): array
    {
        $host = trim($host, '[]');
        if (false !== filter_var($host, FILTER_VALIDATE_IP)) {
            return [$host];
        }

        return [
            ...(gethostbynamel($host) ?: []),
            //A failing DNS query only means no IPv6 address
            ...array_filter(array_column(@dns_get_record($host, DNS_AAAA) ?: [], 'ipv6'), is_string(...)),
        ];
    }

    /**
     * The address of the dashboard of a cluster registered by a client is supplied by the client: it is relayed only
     * over https, to a host whose addresses are all public, never to the private network of Space (SSRF).
     * Redirections are not followed by the HTTP client of the relay.
     */
    private function assertPublicDashboard(string $dashboardUri): void
    {
        $host = parse_url($dashboardUri, PHP_URL_HOST);
        $addresses = [];
        if ('https' === strtolower((string) parse_url($dashboardUri, PHP_URL_SCHEME)) && is_string($host)) {
            $addresses = ($this->hostResolver)($host);
        }

        if (empty($addresses)) {
            throw new BadMethodCallException(
                message: "The dashboard of this cluster must be served over https by a public host",
                code: 403,
            );
        }

        foreach ($addresses as $address) {
            if (false === filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_GLOBAL_RANGE)) {
                throw new BadMethodCallException(
                    message: "The dashboard of this cluster must be served by a public host",
                    code: 403,
                );
            }
        }
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

    private function isStreaming(ServerRequestInterface $serverRequest): bool
    {
        if ('' !== $serverRequest->getHeaderLine('upgrade')) {
            return true;
        }

        parse_str($serverRequest->getUri()->getQuery(), $query);
        foreach (self::STREAMING_PARAMETERS as $parameter) {
            $value = $query[$parameter] ?? null;
            if (is_string($value) && in_array(strtolower($value), ['1', 'true'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Path requested under the frame's URL, as sent by the browser (still encoded).
     */
    private function getRequestedPath(ServerRequestInterface $serverRequest, string $framePath): string
    {
        $path = $serverRequest->getUri()->getPath();
        if (!str_starts_with($path, $framePath)) {
            throw new BadMethodCallException(
                message: "This request is not a request of the dashboard frame",
                code: 400,
            );
        }

        return substr($path, strlen($framePath));
    }

    /**
     * The path is always appended after a `/` closing the dashboard's address: whatever it contains (`@host`,
     * `//host`...), it can not change the host receiving the request, and so the credential.
     */
    private function getDashboardUri(DashboardTarget $target, string $path, string $query): string
    {
        $path = $target->profile->resolvePath(ltrim($path, '/'));
        if (1 === preg_match('#(^|/)\.\.(/|$)#', rawurldecode($path))) {
            throw new BadMethodCallException(
                message: "The dashboard path can not go up from the dashboard address",
                code: 400,
            );
        }

        $uri = rtrim($target->cluster->dashboardAddress, '/') . '/' . $path;
        if ('' !== $query) {
            $uri .= '?' . $query;
        }

        return $uri;
    }

    /**
     * @return array<string, string>
     */
    private function getRequestHeaders(ServerRequestInterface $serverRequest, DashboardTarget $target): array
    {
        $headers = [];
        foreach (self::FORWARDED_REQUEST_HEADERS as $headerName) {
            $value = $serverRequest->getHeaderLine($headerName);
            if ('' !== $value) {
                $headers[$headerName] = $value;
            }
        }

        //Bodies are relayed as is: a compressed one would need its `Content-Encoding`, not relayed
        $headers['Accept-Encoding'] = 'identity';

        return [...$headers, ...$target->profile->renderRequestHeaders($target->token)];
    }

    private function rewriteHtml(string $html, DashboardTarget $target, string $frameUrl): string
    {
        if ($target->profile->rewriteBasePath) {
            $basePath = rtrim((string) parse_url($target->cluster->dashboardAddress, PHP_URL_PATH), '/');
            $framePath = rtrim((string) parse_url($frameUrl, PHP_URL_PATH), '/');

            $html = preg_replace(
                '#' . preg_quote($basePath, '#') . '(?=[/\'"])#',
                $framePath,
                $html,
            ) ?? $html;
        }

        $snippet = $target->profile->renderHeadSnippet($frameUrl, $target->namespace);
        if ('' !== $snippet) {
            $html = preg_replace_callback(
                '#<head\b[^>]*>#i',
                static fn (array $head): string => $head[0] . $snippet,
                $html,
                1,
            ) ?? $html;
        }

        return $html;
    }

    private function createResponse(ResponseInterface $dashboardResponse, string $body): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(
            $dashboardResponse->getStatusCode(),
            $dashboardResponse->getReasonPhrase(),
        );

        foreach (self::FORWARDED_RESPONSE_HEADERS as $headerName) {
            $headersValues = $dashboardResponse->getHeader($headerName);
            if (!empty($headersValues)) {
                $response = $response->withHeader($headerName, $headersValues);
            }
        }

        return $response->withBody($this->streamFactory->createStream($body));
    }

    public function __invoke(
        ManagerInterface $manager,
        EastClient $client,
        ServerRequestInterface $serverRequest,
        DashboardTarget $dashboardTarget,
    ): DashboardFrameInterface {
        $this->assertSameOrigin($serverRequest);

        if ($this->isStreaming($serverRequest)) {
            $client->acceptResponse(
                $this->responseFactory->createResponse(501)->withBody(
                    $this->streamFactory->createStream('Streamed requests are not relayed to the dashboard'),
                ),
            );

            return $this;
        }

        $frameUrl = $this->urlGenerator->generate(
            'space_dashboard_frame',
            [
                'clusterName' => $dashboardTarget->clusterName,
                'envName' => $dashboardTarget->envName,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $dashboardUri = $this->getDashboardUri(
            target: $dashboardTarget,
            path: $this->getRequestedPath($serverRequest, (string) parse_url($frameUrl, PHP_URL_PATH)),
            query: $serverRequest->getUri()->getQuery(),
        );

        if ($dashboardTarget->cluster->isExternal) {
            $this->assertPublicDashboard($dashboardUri);
        }

        try {
            $dashboardResponse = $this->httpMethodsClient->send(
                method: $serverRequest->getMethod(),
                uri: $dashboardUri,
                headers: $this->getRequestHeaders($serverRequest, $dashboardTarget),
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

        $body = (string) $dashboardResponse->getBody();
        if (str_contains(strtolower($dashboardResponse->getHeaderLine('content-type')), 'text/html')) {
            $body = $this->rewriteHtml($body, $dashboardTarget, $frameUrl);
        }

        $client->acceptResponse($this->createResponse($dashboardResponse, $body));

        return $this;
    }
}
