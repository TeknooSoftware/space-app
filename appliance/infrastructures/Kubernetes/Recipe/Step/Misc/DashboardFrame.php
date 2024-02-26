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

namespace Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Misc;

use BadMethodCallException;
use Http\Client\Common\HttpMethodsClientInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Client\ClientInterface as EastClient;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Contracts\Object\Account\AccountAwareInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\DashboardFrameInterface;
use Teknoo\Space\Object\Config\Cluster as ClusterConfig;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\AccountWallet;

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

    private function getDashboardUrl(ClusterConfig $cluster, ?Account $account, string $wildcard): string
    {
        if (!str_contains($wildcard, '#')) {
            return $cluster->dashboardAddress . $wildcard;
        }

        $urlGenerator = new class ($cluster->dashboardAddress . $wildcard) implements AccountAwareInterface {
            public function __construct(
                public string $url,
            ) {
            }

            public function passAccountNamespace(
                Account $account,
                ?string $name,
                ?string $namespace,
                ?string $prefixNamespace,
                bool $useHierarchicalNamespaces,
            ): AccountAwareInterface {
                $kubeNamespace = $prefixNamespace . $namespace;
                $this->url .= '?' . http_build_query([
                        'namespace' => $kubeNamespace
                    ]);

                return $this;
            }
        };

        if (null !== $account) {
            $account->requireAccountNamespace($urlGenerator);
        } else {
            $urlGenerator->url .= '?' . http_build_query([
                'namespace' => '_all',
            ]);
        }

        return $urlGenerator->url;
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
    ): DashboardFrameInterface {
        if (empty($wildcard)) {
            $wildcard = '#/workloads';
        }

        $clusterConfig = $this->catalog->getCluster($clusterName);

        $isAdmin = in_array('ROLE_ADMIN', (array) $user->getRoles());
        $accountCredential = null;

        if (!$isAdmin) {
            if (null === $accountWallet) {
                throw new BadMethodCallException(message: "Wallet is mandatory for non admin user", code: 403);
            }

            if (!isset($accountWallet[$clusterConfig->name])) {
                throw new BadMethodCallException(message: "Cluster is not allowed for this user", code: 403);
            }

            $accountCredential = $accountWallet[$clusterConfig->name];
        }

        $dashboardUrl = $this->getDashboardUrl($clusterConfig, $account, $wildcard);

        $responseDashboard = $this->httpMethodsClient->send(
            method: $serverRequest->getMethod(),
            uri: $dashboardUrl,
            headers: [
                'Authorization' => 'Bearer ' . trim($accountCredential?->getToken() ?? $clusterConfig->token),
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
