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

namespace Teknoo\Space\App\Config;

use ArrayObject;
use DomainException;
use Psr\Container\ContainerInterface;
use Teknoo\East\Paas\Infrastructures\Kubernetes\Contracts\ClientFactoryInterface;
use Teknoo\East\Paas\Infrastructures\Kubernetes\Transcriber\IngressTranscriber as BaseIngressTranscriber;
use Teknoo\East\Paas\Object\ClusterCredentials;
use Teknoo\Kubernetes\RepositoryRegistry;
use Teknoo\Space\Infrastructures\Kubernetes\Transcriber\IngressTranscriber;
use Teknoo\Space\Object\Config\Cluster;
use Teknoo\Space\Object\Config\ClusterCatalog;

use function DI\env;
use function preg_replace;
use function strtolower;
use function trim;

return [
    'teknoo.space.kubernetes.default_cluster.master' => env('SPACE_KUBERNETES_MASTER', null),
    'teknoo.space.kubernetes.default_cluster.dashboard' => env('SPACE_KUBERNETES_DASHBOARD', null),
    'teknoo.space.kubernetes.default_cluster.create_account.token' => env('SPACE_KUBERNETES_CREATE_TOKEN', null),
    'teknoo.space.kubernetes.default_cluster.create_account.ca_cert' => env('SPACE_KUBERNETES_CA_VALUE', null),
    'teknoo.space.kubernetes.default_cluster.name' => env('SPACE_KUBERNETES_CLUSTER_NAME', 'localhost'),
    'teknoo.space.kubernetes.default_cluster.type' => env('SPACE_KUBERNETES_CLUSTER_TYPE', 'kubernetes'),
    'teknoo.space.kubernetes.default_cluster.use_hnc' => env('SPACE_KUBERNETES_CLUSTER_USE_HNC', false),

    BaseIngressTranscriber::class . ':class' => IngressTranscriber::class,

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

        $master = $container->get('teknoo.space.kubernetes.default_cluster.master');
        $clusterName = $container->get('teknoo.space.kubernetes.default_cluster.name');

        if (empty($definitions) && !empty($clusterName) && !empty($master)) {
            $definitions = [
                [
                    'master' => $master,
                    'dashboard' => $container->get('teknoo.space.kubernetes.default_cluster.dashboard'),
                    'create_account' => [
                        'token' => $container->get('teknoo.space.kubernetes.default_cluster.create_account.token'),
                        'ca_cert' => $container->get('teknoo.space.kubernetes.default_cluster.create_account.ca_cert'),
                    ],
                    'name' => $clusterName,
                    'type' => $container->get('teknoo.space.kubernetes.default_cluster.type'),
                    'support_registry' => true,
                    'use_hnc' => $container->get('teknoo.space.kubernetes.default_cluster.use_hnc'),
                ]
            ];
        }

        $storageProvisioner = $container->get('teknoo.east.paas.default_storage_provider');
        $factory = $container->get(ClientFactoryInterface::class);

        $sluggyfier = fn ($text) => strtolower(trim((string) preg_replace('#[^A-Za-z0-9-]+#', '-', $text)));

        $clustersList = [];
        $aliases = [];

        foreach ($definitions as $definition) {
            $name = (string) $definition['name'];
            if (isset($clustersList[$name])) {
                throw new DomainException("Error, the cluster $name is already defined in the catalog");
            }

            $caCertificate = base64_decode($definition['create_account']['ca_cert']);
            $credentials = new ClusterCredentials(
                caCertificate: $caCertificate,
                token: $definition['create_account']['token'],
            );

            $clientInit = fn() => $factory(
                $definition['master'],
                $credentials,
                $container->get(RepositoryRegistry::class)
            );
            $sluggyName = $sluggyfier($definition['name']);
            $aliases[$sluggyName] = $name;
            $clustersList[$name] = new Cluster(
                name: $name,
                sluggyName: $sluggyName,
                type: $definition['type'],
                masterAddress: $definition['master'],
                storageProvisioner: $definition['storage_provisioner'] ?? $storageProvisioner,
                dashboardAddress: $definition['dashboard'] ?? '',
                kubernetesClient: $clientInit,
                token: $definition['create_account']['token'],
                supportRegistry: !empty($definition['support_registry']),
                useHnc: !empty($definition['use_hnc']),
            );
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
