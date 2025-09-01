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

namespace Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Client;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teknoo\East\Common\Recipe\Step\Traits\ResponseTrait;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SetRedirectClientAtEnd
{
    use ResponseTrait;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        private UrlGeneratorInterface $router,
    ) {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param array<string, string|int> $parameters
     */
    private function generateUrl(
        string $route,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        return $this->router->generate($route, $parameters, $referenceType);
    }

    private function redirect(
        string $url,
        int $status
    ): ResponseInterface {
        $response = $this->responseFactory->createResponse($status);

        $headers = ['location' => $url ];
        $response = $this->addHeadersIntoResponse($response, $headers);

        return $response;
    }

    /**
     * @param array<string, string|int> $parameters
     */
    public function __invoke(
        ManagerInterface $manager,
        ClientInterface $client,
        string $route,
        int $status = 302,
        array $parameters = []
    ): SetRedirectClientAtEnd {
        $response = $this->redirect(
            $this->generateUrl(
                $route,
                $parameters
            ),
            $status,
        );

        $client->acceptResponse($response);

        return $this;
    }
}
