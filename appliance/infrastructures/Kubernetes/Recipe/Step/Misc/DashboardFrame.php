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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Misc;

use BadMethodCallException;
use Http\Client\Common\HttpMethodsClientInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Client\ClientInterface as EastClient;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\DashboardFrameInterface;
use Teknoo\Space\Object\Config\Cluster as ClusterConfig;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\Persisted\AccountEnvironment;

use function http_build_query;
use function in_array;
use function str_contains;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class DashboardFrame implements DashboardFrameInterface
{
    public function __construct(
        private ClusterCatalog $catalog,
        private HttpMethodsClientInterface $httpMethodsClient,
        private ResponseFactoryInterface $responseFactory,
    ) {
    }

    private function getDashboardUrl(ClusterConfig $cluster, ?AccountEnvironment $env, string $wildcard): string
    {
        if (!str_contains($wildcard, '#')) {
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

    public function __invoke(
        ManagerInterface $manager,
        EastClient $client,
        ServerRequestInterface $serverRequest,
        User $user,
        string $clusterName,
        string $wildcard = '',
        ?Account $account = null,
        ?AccountWallet $accountWallet = null,
        ?string $envName = null,
    ): DashboardFrameInterface {
        if (empty($wildcard)) {
            $wildcard = '#/workloads';
        }

        $clusterConfig = $this->catalog->getCluster($clusterName);

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

        $responseDashboard = $this->httpMethodsClient->send(
            method: $serverRequest->getMethod(),
            uri: $dashboardUrl,
            headers: [
                'Authorization' => 'Bearer ' . trim($accountEnvironment?->getToken() ?? $clusterConfig->token),
            ],
        );

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

        $response = $response->withBody(
            $responseDashboard->getBody(),
        );

        $client->acceptResponse($response);

        return $this;
    }
}
