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
 * @link        https://teknoo.software/applications/space Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Behat\Traits;

use Behat\Step\Then;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\UserWithRecoveryAccess;

use function array_merge;
use function str_replace;
use function str_starts_with;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
trait HttpTrait
{
    /**
     * @param Cookie[] $cookies
     * @return array
     */
    public function extractCookies(array &$cookies): array
    {
        $final = [];
        foreach ($cookies as $cookie) {
            $final[$cookie->getName()] = $cookie->getValue();
        }

        return $final;
    }

    public function getPathFromRoute(string $route, array $parameters = []): string
    {
        return $this->urlGenerator->generate(
            name: $route,
            parameters: $parameters,
        );
    }

    public function executeRequest(
        string $method,
        string $url,
        array $params = [],
        array $headers = [],
        bool $noCookies = false,
        bool $clearCookies = false,
        ?string $content = null,
    ): Response {
        $this->hasBeenRedirected = false;
        $this->isApiCall = !empty($headers['HTTP_AUTHORIZATION']);

        $host = $originalHost = 'https://' . $this->appHostname;
        if (true === str_starts_with($url, 'http')) {
            $host = '';
        }

        $cookies = [];
        if (false === $noCookies) {
            $cookies = $this->cookies;
        }

        $this->currentUrl = str_replace($originalHost, '', $url);

        $this->request = Request::create(
            uri: $host . $url,
            method: $method,
            parameters: $params,
            cookies: $cookies,
            server: array_merge(
                [
                    'REMOTE_ADDR' => $this->clientIp,
                ],
                $headers,
            ),
            content: $content,
        );

        $this->response = $this->kernel->handle($this->request);

        if (true === $clearCookies) {
            $this->cookies = [];
        } elseif (
            false === $clearCookies
            && false === $noCookies
            && !empty($cookies = $this->response->headers->getCookies())
        ) {
            $cookies = array_merge($this->cookies, $this->extractCookies($cookies));

            //Exclude null cookies, must be deleted
            $this->cookies = [];
            foreach ($cookies as $name => $value) {
                if (null !== $value) {
                    $this->cookies[$name] = $value;
                }
            }
        }

        if (302 === $this->response->getStatusCode()) {
            if ($this->response instanceof RedirectResponse) {
                $newUrl = $this->response->getTargetUrl();
            } else {
                $newUrl = $this->response->headers->get('location');
            }

            if (isset($headers['CONTENT_TYPE'])) {
                unset($headers['CONTENT_TYPE']);
            }

            $response = $this->executeRequest(
                method: 'GET',
                url: $newUrl,
                headers: $headers,
            );
            $this->hasBeenRedirected = true;

            return $response;
        }

        return $this->response;
    }

    #[Then('a session is opened')]
    #[Then('a new session is open')]
    public function aNewSessionIsOpen(): void
    {
        Assert::assertNotEmpty($token = $this->getTokenStorageService->tokenStorage?->getToken());
        Assert::assertInstanceOf(PasswordAuthenticatedUser::class, $token?->getUser());
    }

    #[Then('a recovery session is opened')]
    public function aNewRecoverySessionIsOpen(): void
    {
        Assert::assertNotEmpty($token = $this->getTokenStorageService->tokenStorage?->getToken());
        Assert::assertInstanceOf(UserWithRecoveryAccess::class, $token?->getUser());
    }

    #[Then('a session must be not opened')]
    public function aSessionMustBeNotOpened(): void
    {
        Assert::assertEmpty($this->getTokenStorageService->tokenStorage?->getToken());
    }
}
