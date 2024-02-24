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
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Behat;

use Http\Client\HttpClient;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Teknoo\Kubernetes\HttpClient\InstantiatorInterface;

use function array_pop;
use function explode;
use function json_decode;

class MockClientInstantiator implements InstantiatorInterface
{
    public static TestsContext $testsContext;

    public function build(
        bool $verify,
        ?string $caCertificate,
        ?string $clientCertificate,
        ?string $clientKey,
        ?int $timeout,
    ): ClientInterface {
        return new class (self::$testsContext) implements HttpClient {
            public function __construct(
                private readonly TestsContext $testsContext,
            ) {
            }

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                if ('GET' !== $request->getMethod()) {
                    $uri = (string) $request->getUri();
                    $uriPars = explode('v1', $uri);
                    $model = trim(array_pop($uriPars), '/');

                    $body = (string) $request->getBody();

                    $this->testsContext->setManifests($model, json_decode($body, true));
                }

                return new JsonResponse(['items' => []]);
            }
        };
    }
}
