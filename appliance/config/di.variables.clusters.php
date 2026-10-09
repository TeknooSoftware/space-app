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

namespace Teknoo\Space\App\Config;

use ArrayObject;
use DomainException;
use Psr\Container\ContainerInterface;
use Teknoo\East\Paas\Infrastructures\Kubernetes\Contracts\ClientFactoryInterface;
use Teknoo\East\Paas\Infrastructures\Kubernetes\Transcriber\IngressTranscriber as BaseIngressTranscriber;
use Teknoo\East\Paas\Object\ClusterCredentials;
use Teknoo\Kubernetes\RepositoryRegistry;
use Teknoo\Space\Infrastructures\Kubernetes\Transcriber\IngressTranscriber;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\DashboardProfileCatalog;
use Teknoo\Space\Object\Config\DashboardProfile;
use Teknoo\Space\Object\Config\DockerComposeCluster;
use Teknoo\Space\Object\Config\Exception\UnsupportedClusterTypeException;
use Teknoo\Space\Object\Config\KubernetesCluster;

use function DI\env;
use function filter_var;
use function preg_replace;
use function strtolower;
use function trim;

use const FILTER_VALIDATE_BOOL;

return [
    'teknoo.space.clusters.default_cluster.master' => env('SPACE_KUBERNETES_MASTER'),
    'teknoo.space.clusters.default_cluster.dashboard' => env('SPACE_KUBERNETES_DASHBOARD'),
    'teknoo.space.clusters.default_cluster.create_account.token' => env('SPACE_KUBERNETES_CREATE_TOKEN'),
    'teknoo.space.clusters.default_cluster.create_account.ca_cert' => env('SPACE_KUBERNETES_CA_VALUE'),
    'teknoo.space.clusters.default_cluster.name' => env('SPACE_CLUSTER_NAME', 'localhost'),
    'teknoo.space.clusters.default_cluster.type' => env('SPACE_CLUSTER_TYPE', 'kubernetes'),
    'teknoo.space.clusters.default_cluster.use_hnc' => env('SPACE_KUBERNETES_CLUSTER_USE_HNC', false),

    BaseIngressTranscriber::class . ':class' => IngressTranscriber::class,

    //Kubernetes web dashboards embedded by Space: a cluster selects one with its `dashboard_type` key, otherwise the
    //default one is used. An extension can add its own profiles by decorating `teknoo.space.dashboard.profiles`.
    //(an ArrayObject, the Symfony bridge requiring an object for each entry)
    'teknoo.space.dashboard.default_type' => env('SPACE_KUBERNETES_DASHBOARD_TYPE', 'headlamp'),
    //The dashboard of a cluster registered by a client has an address supplied by the client: the web process
    //relays it only when the operator allows it. Off by default. Resolved here (not through env()) so "false" is a
    //boolean false, not a non-empty string.
    'teknoo.space.dashboard.external.enabled' => filter_var(
        $_ENV['SPACE_DASHBOARD_EXTERNAL_ENABLED'] ?? false,
        FILTER_VALIDATE_BOOL,
    ),
    'teknoo.space.dashboard.profiles' => static fn (): ArrayObject => new ArrayObject([
        //Headlamp must be served under a base path (Helm value `config.baseURL`, e.g. `/__headlamp`), included in
        //the dashboard address: it is replaced, in the pages relayed, by the path of the frame. The namespaces
        //allowed to the user are given to Headlamp through its cluster settings (local storage).
        'headlamp' => new DashboardProfile(
            name: 'headlamp',
            requestHeaders: ['Authorization' => 'Bearer {token}'],
            entryPath: 'c/main',
            namespacedEntryPath: 'c/main/workloads?namespace={namespace}',
            headSnippet: '<script>(function(s){try{var k=\'cluster_settings.main\','
                . 'c=JSON.parse(s.getItem(k)||\'{}\');delete c.allowedNamespaces;delete c.defaultNamespace;'
                . 's.setItem(k,JSON.stringify(c));s.removeItem(\'headlamp-selected-namespace_main\');}'
                . 'catch(e){}})(window.localStorage);</script>',
            namespacedHeadSnippet: '<script>(function(s,n){try{var k=\'cluster_settings.main\','
                . 'c=JSON.parse(s.getItem(k)||\'{}\');c.allowedNamespaces=[n];c.defaultNamespace=n;'
                . 's.setItem(k,JSON.stringify(c));}catch(e){}})(window.localStorage,\'{namespace}\');</script>',
            rewriteBasePath: true,
        ),
        //The legacy Kubernetes Dashboard (archived), selecting the namespace through its URL fragment
        'kubernetes-dashboard' => new DashboardProfile(
            name: 'kubernetes-dashboard',
            requestHeaders: ['Authorization' => 'Bearer {token}'],
            entryPath: '#/workloads?namespace=_all',
            namespacedEntryPath: '#/workloads?namespace={namespace}',
            headSnippet: '<base href="{baseHref}">',
            namespacedHeadSnippet: '<base href="{baseHref}">',
            pathAliases: ['config/config.json' => 'assets/config/config.json'],
        ),
    ]),
    DashboardProfileCatalog::class => static function (ContainerInterface $container): DashboardProfileCatalog {
        $profiles = $container->get('teknoo.space.dashboard.profiles');
        if ($profiles instanceof ArrayObject) {
            $profiles = $profiles->getArrayCopy();
        }

        $defaultType = trim((string) $container->get('teknoo.space.dashboard.default_type'));

        return new DashboardProfileCatalog(
            profiles: $profiles,
            defaultType: '' !== $defaultType ? $defaultType : 'headlamp',
        );
    },

    'teknoo.space.clusters_catalog' => static function (ContainerInterface $container): ClusterCatalog {
        static $clusterCatalog = null;
        if (null !== $clusterCatalog) {
            return $clusterCatalog;
        }

        $definitions = [];
        if ($container->has('teknoo.space.clusters_catalog.definitions')) {
            $definitions = $container->get('teknoo.space.clusters_catalog.definitions');

            if ($definitions instanceof ArrayObject) {
                $definitions = $definitions->getArrayCopy();
            }
        }

        $master = $container->get('teknoo.space.clusters.default_cluster.master');
        $clusterName = $container->get('teknoo.space.clusters.default_cluster.name');

        if (empty($definitions) && !empty($clusterName) && !empty($master)) {
            $definitions = [
                [
                    'master' => $master,
                    'dashboard' => $container->get('teknoo.space.clusters.default_cluster.dashboard'),
                    'create_account' => [
                        'token' => $container->get('teknoo.space.clusters.default_cluster.create_account.token'),
                        'ca_cert' => $container->get('teknoo.space.clusters.default_cluster.create_account.ca_cert'),
                    ],
                    'name' => $clusterName,
                    'type' => $container->get('teknoo.space.clusters.default_cluster.type'),
                    'support_registry' => true,
                    'use_hnc' => $container->get('teknoo.space.clusters.default_cluster.use_hnc'),
                ]
            ];
        }

        $storageProvisioner = $container->get('teknoo.east.paas.default_storage_provider');
        $factory = $container->get(ClientFactoryInterface::class);

        $sluggyfier = fn ($text) => strtolower(trim((string) preg_replace('#[^A-Za-z0-9-]+#', '-', (string) $text)));

        $buildDockerComposeCluster = static function (
            array $definition,
            string $name,
            string $sluggyName,
            string $type,
        ): DockerComposeCluster {
            if (empty($definition['ssh']['client_key'])) {
                throw new DomainException(
                    "Error, the docker-compose cluster $name requires an ssh.client_key in the catalog"
                );
            }

            return new DockerComposeCluster(
                name: $name,
                sluggyName: $sluggyName,
                type: $type,
                masterAddress: (string) $definition['master'],
                dashboardAddress: (string) ($definition['dashboard'] ?? ''),
                isExternal: !empty($definition['is_external']),
                clientKey: (string) $definition['ssh']['client_key'],
                username: (string) ($definition['ssh']['username'] ?? ''),
                caCertificate: (string) ($definition['ssh']['known_hosts'] ?? ''),
                supportRegistry: (bool) ($definition['support_registry'] ?? true),
            );
        };

        $buildKubernetesCluster = static function (
            array $definition,
            string $name,
            string $sluggyName,
            string $type,
        ) use (
            $container,
            $factory,
            $storageProvisioner
): KubernetesCluster {
            $caCertificate = base64_decode((string) $definition['create_account']['ca_cert']);
            $credentials = new ClusterCredentials(
                caCertificate: $caCertificate,
                token: $definition['create_account']['token'],
            );

            $clientInit = fn () => $factory(
                $definition['master'],
                $credentials,
                $container->get(RepositoryRegistry::class)
            );

            $dashboardType = (string) ($definition['dashboard_type'] ?? '');
            if ('' !== $dashboardType) {
                //An unknown dashboard type fails at boot, not at the first opening of the dashboard
                $container->get(DashboardProfileCatalog::class)->getProfile($dashboardType);
            }

            return new KubernetesCluster(
                name: $name,
                sluggyName: $sluggyName,
                type: $type,
                masterAddress: $definition['master'],
                storageProvisioner: $definition['storage_provisioner'] ?? $storageProvisioner,
                dashboardAddress: $definition['dashboard'] ?? '',
                kubernetesClient: $clientInit,
                token: $definition['create_account']['token'],
                supportRegistry: !empty($definition['support_registry']),
                useHnc: !empty($definition['use_hnc']),
                isExternal: false,
                dashboardType: $dashboardType,
            );
        };

        $clustersList = [];
        $aliases = [];

        foreach ($definitions as $definition) {
            $name = (string) $definition['name'];
            if (isset($clustersList[$name])) {
                throw new DomainException("Error, the cluster $name is already defined in the catalog");
            }

            $sluggyName = $sluggyfier($definition['name']);
            $aliases[$sluggyName] = $name;

            $type = (string) ($definition['type'] ?? 'kubernetes');

            $clustersList[$name] = match ($type) {
                'docker-compose' => $buildDockerComposeCluster($definition, $name, $sluggyName, $type),
                'kubernetes' => $buildKubernetesCluster($definition, $name, $sluggyName, $type),
                default => throw new UnsupportedClusterTypeException(
                    "Error, the cluster $name uses an unsupported cluster type '$type'"
                ),
            };
        }

        return $clusterCatalog = new ClusterCatalog($clustersList, $aliases);
    },

    //Generic
    'teknoo.space.kubernetes.root_namespace' => env(
        'SPACE_KUBERNETES_ROOT_NAMESPACE',
        'space-client-',
    ),
    'teknoo.space.kubernetes.registry_root_namespace' => env(
        'SPACE_KUBERNETES_REGISTRY_ROOT_NAMESPACE',
        'space-registry-',
    ),
    'teknoo.space.kubernetes.cluster_issuer' => env('SPACE_CLUSTER_ISSUER'),
    'teknoo.space.kubernetes.secret_account_token_waiting_time' => env(
        'SPACE_KUBERNETES_SECRET_ACCOUNT_TOKEN_WAITING_TIME',
        1,
    ),

    'teknoo.space.kubernetes.oci_registry.image' => env('SPACE_OCI_REGISTRY_IMAGE', 'registry:latest'),
    'teknoo.space.kubernetes.oci_registry.requests.cpu' => env('SPACE_OCI_REGISTRY_REQUESTS_CPU', '10m'),
    'teknoo.space.kubernetes.oci_registry.requests.memory' => env('SPACE_OCI_REGISTRY_REQUESTS_MEMORY', '30Mi'),
    'teknoo.space.kubernetes.oci_registry.limits.cpu' => env('SPACE_OCI_REGISTRY_LIMITS_CPU', '100m'),
    'teknoo.space.kubernetes.oci_registry.limits.memory' => env('SPACE_OCI_REGISTRY_LIMITS_MEMORY', '256Mi'),
    'teknoo.space.kubernetes.oci_registry.url' => env('SPACE_OCI_REGISTRY_URL'),
    'teknoo.space.kubernetes.oci_registry.tls_secret_name' => env('SPACE_OCI_REGISTRY_TLS_SECRET'),
    'teknoo.space.kubernetes.oci_registry.storage_claiming_size' => env('SPACE_OCI_REGISTRY_PVC_SIZE'),

    'teknoo.space.kubernetes.oci_space_global_registry.url' => env('SPACE_OCI_GLOBAL_REGISTRY_URL'),
    'teknoo.space.kubernetes.oci_space_global_registry.username' => env('SPACE_OCI_GLOBAL_REGISTRY_USERNAME'),
    'teknoo.space.kubernetes.oci_space_global_registry.pwd' => env('SPACE_OCI_GLOBAL_REGISTRY_PWD'),
];
