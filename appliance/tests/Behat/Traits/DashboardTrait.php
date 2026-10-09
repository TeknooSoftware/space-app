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

namespace Teknoo\Space\Tests\Behat\Traits;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Hook\AfterScenario;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use function str_contains;

/**
 * Fake web dashboard of the Kubernetes clusters (hosts `dashboard.*`, answered by the MockClientInstantiator),
 * served like Headlamp under the base path `/__headlamp`, and steps to use and check the dashboard relay of Space.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait DashboardTrait
{
    private ?RequestInterface $dashboardRequest = null;

    private ?string $previousDefaultDashboardType = null;

    private bool $defaultDashboardTypeChanged = false;

    public function answerAsTheDashboard(RequestInterface $request): ResponseInterface
    {
        $this->dashboardRequest = $request;

        if (str_contains($request->getUri()->getPath(), '/clusters/')) {
            return new JsonResponse(['kind' => 'SelfSubjectRulesReview', 'status' => []], 201);
        }

        return new HtmlResponse(
            '<!doctype html><html><head><meta charset="utf-8">'
            . '<script>window.headlampBaseUrl = \'/__headlamp\';</script></head>'
            . '<body><script src="/__headlamp/assets/index.js"></script></body></html>',
        );
    }

    #[Given('the default dashboard is the legacy Kubernetes Dashboard')]
    public function theDefaultDashboardIsTheLegacyKubernetesDashboard(): void
    {
        $this->defaultDashboardTypeChanged = true;
        $this->previousDefaultDashboardType = $_ENV['SPACE_KUBERNETES_DASHBOARD_TYPE'] ?? null;
        $_ENV['SPACE_KUBERNETES_DASHBOARD_TYPE'] = 'kubernetes-dashboard';
    }

    #[AfterScenario]
    public function restoreTheDefaultDashboard(): void
    {
        if (!$this->defaultDashboardTypeChanged) {
            return;
        }

        unset($_ENV['SPACE_KUBERNETES_DASHBOARD_TYPE']);
        if (null !== $this->previousDefaultDashboardType) {
            $_ENV['SPACE_KUBERNETES_DASHBOARD_TYPE'] = $this->previousDefaultDashboardType;
        }

        $this->defaultDashboardTypeChanged = false;
    }

    /**
     * @param array<string, string> $headers
     */
    private function requestTheDashboardFrame(
        string $method,
        string $path,
        string $clusterName,
        string $envName,
        array $headers = [],
        ?string $body = null,
    ): void {
        $this->executeRequest(
            method: $method,
            url: $this->getPathFromRoute(
                route: 'space_dashboard_frame',
                parameters: ['clusterName' => $clusterName, 'envName' => $envName],
            ) . $path,
            headers: $headers,
            content: $body,
        );
    }

    #[When('It sends a :method request to :path on the dashboard frame of :clusterName for :envName')]
    public function itSendsARequestToOnTheDashboardFrameOfFor(
        string $method,
        string $path,
        string $clusterName,
        string $envName,
        ?PyStringNode $body = null,
    ): void {
        $headers = [];
        if (null !== $body) {
            $headers['CONTENT_TYPE'] = 'application/json';
        }

        $this->requestTheDashboardFrame(
            method: $method,
            path: $path,
            clusterName: $clusterName,
            envName: $envName,
            headers: $headers,
            body: $body?->getRaw(),
        );
    }

    #[When('another site sends a :method request to :path on the dashboard frame of :clusterName for :envName')]
    public function anotherSiteSendsARequestToOnTheDashboardFrameOfFor(
        string $method,
        string $path,
        string $clusterName,
        string $envName,
    ): void {
        $this->requestTheDashboardFrame(
            method: $method,
            path: $path,
            clusterName: $clusterName,
            envName: $envName,
            headers: ['HTTP_ORIGIN' => 'https://evil.test', 'HTTP_SEC_FETCH_SITE' => 'cross-site'],
        );
    }

    #[Then('the dashboard received a :method request to :uri with the token :token')]
    public function theDashboardReceivedARequestToWithTheToken(string $method, string $uri, string $token): void
    {
        Assert::assertNotNull($this->dashboardRequest, 'The dashboard was not reached');
        Assert::assertSame($method, $this->dashboardRequest->getMethod());
        Assert::assertSame($uri, (string) $this->dashboardRequest->getUri());
        Assert::assertSame('Bearer ' . $token, $this->dashboardRequest->getHeaderLine('Authorization'));
        Assert::assertSame('', $this->dashboardRequest->getHeaderLine('Cookie'));
    }

    #[Then('the dashboard received the body:')]
    public function theDashboardReceivedTheBody(PyStringNode $body): void
    {
        Assert::assertSame($body->getRaw(), (string) $this->dashboardRequest?->getBody());
    }

    #[Then('the dashboard was not reached')]
    public function theDashboardWasNotReached(): void
    {
        Assert::assertNull($this->dashboardRequest);
    }

    private function getRelayedPage(): string
    {
        $this->isAFinalResponse();

        return (string) $this->response?->getContent();
    }

    #[Then('the dashboard page is served under :framePath')]
    public function theDashboardPageIsServedUnder(string $framePath): void
    {
        $page = $this->getRelayedPage();

        Assert::assertStringContainsString("headlampBaseUrl = '{$framePath}'", $page);
        Assert::assertStringContainsString("src=\"{$framePath}/assets/index.js\"", $page);
        Assert::assertStringNotContainsString('/__headlamp', $page);
    }

    #[Then('the dashboard page restricts Headlamp to the namespace :namespace')]
    public function theDashboardPageRestrictsHeadlampToTheNamespace(string $namespace): void
    {
        $page = $this->getRelayedPage();

        Assert::assertStringContainsString('c.allowedNamespaces=[n];c.defaultNamespace=n;', $page);
        Assert::assertStringContainsString("(window.localStorage,'{$namespace}')", $page);
    }

    #[Then('the dashboard page lets Headlamp show all namespaces')]
    public function theDashboardPageLetsHeadlampShowAllNamespaces(): void
    {
        Assert::assertStringContainsString(
            'delete c.allowedNamespaces;delete c.defaultNamespace;',
            $this->getRelayedPage(),
        );
    }

    #[Then('the dashboard page has the base :href')]
    public function theDashboardPageHasTheBase(string $href): void
    {
        Assert::assertStringContainsString("<head><base href=\"{$href}\">", $this->getRelayedPage());
    }
}
