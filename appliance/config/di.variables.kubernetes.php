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

namespace App\Config;

use function DI\env;

return [
    'teknoo.space.kubernetes.root_namespace' => env('SPACE_KUBERNETES_ROOT_NAMESPACE'),
    'teknoo.space.kubernetes.master' => env('SPACE_KUBERNETES_MASTER'),
    'teknoo.space.kubernetes.dashboard' => env('SPACE_KUBERNETES_DASHBOARD'),
    'teknoo.space.kubernetes.create_account.token' => env('SPACE_KUBERNETES_CREATE_TOKEN'),
    'teknoo.space.kubernetes.create_account.ca_cert' => env('SPACE_KUBERNETES_CA_VALUE'),
    'teknoo.space.kubernetes.cluster.default_name' => env('SPACE_KUBERNETES_CLUSTER_NAME', 'localhost'),
    'teknoo.space.kubernetes.cluster.default_type' => env('SPACE_KUBERNETES_CLUSTER_TYPE', 'kubernetes'),
    'teknoo.space.kubernetes.cluster.default_env' => env('SPACE_KUBERNETES_CLUSTER_ENV', 'prod'),
    'teknoo.space.kubernetes.secret_account_token_waiting_time' => env(
        'SPACE_KUBERNETES_SECRET_ACCOUNT_TOKEN_WAITING_TIME',
        1000,
    ),

    'teknoo.space.kubernetes.oci_registry.image' => env('SPACE_OCI_REGISTRY_IMAGE'),
    'teknoo.space.kubernetes.oci_registry.url' => env('SPACE_OCI_REGISTRY_URL'),
    'teknoo.space.kubernetes.oci_registry.tls_secret_name' => env('SPACE_OCI_REGISTRY_TLS_SECRET'),
    'teknoo.space.kubernetes.oci_registry.storage_claiming_size' => env('SPACE_OCI_REGISTRY_PVC_SIZE'),

    'teknoo.space.kubernetes.oci_space_global_registry.url' => env('SPACE_OCI_GLOBAL_REGISTRY_URL'),
    'teknoo.space.kubernetes.oci_space_global_registry.username' => env('SPACE_OCI_GLOBAL_REGISTRY_USERNAME'),
    'teknoo.space.kubernetes.oci_space_global_registry.pwd' => env('SPACE_OCI_GLOBAL_REGISTRY_PWD'),

    'teknoo.space.kubernetes.cluster_issuer' => env('SPACE_CLUSTER_ISSUER'),
];
