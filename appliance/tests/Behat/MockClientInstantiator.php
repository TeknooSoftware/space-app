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

namespace Teknoo\Space\Tests\Behat;

use Http\Client\HttpClient;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Teknoo\Kubernetes\HttpClient\InstantiatorInterface;
use Teknoo\Space\Object\Persisted\AccountEnvironment;

use function array_pop;
use function explode;
use function json_decode;
use function parse_str;
use function str_contains;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
class MockClientInstantiator implements InstantiatorInterface
{
    public static SpaceContext $testsContext;

    public function build(
        bool $verify,
        ?string $caCertificate,
        ?string $clientCertificate,
        ?string $clientKey,
        ?int $timeout,
    ): ClientInterface {
        return new class (self::$testsContext) implements HttpClient {
            public function __construct(
                private readonly SpaceContext $testsContext,
                private int $counter = 0,
            ) {
            }

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                if ('GET' !== $request->getMethod()) {
                    $uriStr = (string) $request->getUri();
                    $host = $request->getUri()->getHost();
                    $uriPars = explode('v1', $uriStr);
                    $model = trim(array_pop($uriPars), '/');

                    $body = (string) $request->getBody();

                    if ('DELETE' === $request->getMethod()) {
                        $a = explode('/', $model);
                        $this->testsContext->setDeletedManifests($host, array_pop($a));
                    } else {
                        $this->testsContext->setManifests($host, $model, json_decode($body, true));
                    }
                }

                $uri = $request->getUri();
                $path = $uri->getPath();
                $query = $uri->getQuery();

                if ('/api/v1/namespaces' === $path) {
                    $qs = [];
                    parse_str($query, $qs);
                    if (!empty($qs['labelSelector'])) {
                        foreach ($this->testsContext->listObjects(AccountEnvironment::class) as $env) {
                            if ('name=' . $env->getNamespace() === $qs['labelSelector']) {
                                return new JsonResponse(
                                    [
                                        'items' => [
                                            [
                                                'metadata' => [
                                                    'name' => $env->getNamespace(),
                                                    'labels' => [
                                                        'name' => $env->getNamespace(),
                                                        'id' => $env->getAccount()->getId(),
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                );
                            }
                        }
                    }
                }

                if (
                    str_contains($path, '/secrets')
                    && (str_contains($query, 'behat-secret') || str_contains($query, 'my-company-secret'))
                    && $this->counter++ > 1
                ) {
                    return new JsonResponse(
                        [
                            'items' => [
                                [
                                    'metadata' => [
                                        'name' => 'behat-secret',
                                        'namespace' => 'space-client-behat',
                                        'labels' => ['name' => 'behat-secret'],
                                        'annotations' => [
                                            'kubernetes.io/service-account.name' => 'behat-account'
                                        ],
                                    ],
                                    'type' => 'kubernetes.io/service-account-token',
                                    'data' => [
                                        'token' => 'foo',
                                        'ca.crt' => 'bar',
                                    ],
                                ],
                            ],
                        ],
                    );
                }

                return new JsonResponse(['items' => []]);
            }
        };
    }
}
