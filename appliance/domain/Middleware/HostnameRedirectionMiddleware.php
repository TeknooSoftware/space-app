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

namespace Teknoo\Space\Middleware;

use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;

use function mb_strtolower;
use function str_starts_with;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class HostnameRedirectionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private string $allowedHost,
    ) {
    }

    public function execute(
        ClientInterface $client,
        MessageInterface $message,
        ManagerInterface $manager
    ): MiddlewareInterface {
        if (!$message instanceof ServerRequestInterface) {
            return $this;
        }

        $uri = $message->getUri();
        $hostname = $uri->getHost();

        if (
            mb_strtolower($hostname) === mb_strtolower($this->allowedHost)
            || str_starts_with($hostname, '10.')
            || str_starts_with($hostname, '172.16.')
            || str_starts_with($hostname, '127.')
            || str_starts_with($hostname, '192.168.')
        ) {
            return $this;
        }

        $uri = $uri->withHost($this->allowedHost);
        $redirectResponse = new RedirectResponse($uri, 302);

        $client->acceptResponse($redirectResponse);
        $manager->stop();

        return $this;
    }
}
