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

use Psr\Container\ContainerInterface;

use function DI\env;
use function DI\get;
use function dirname;
use function filter_var;
use function preg_match;
use function strtolower;

use const FILTER_VALIDATE_BOOL;

$parameters = [
    //East PaaS Configuration
    'teknoo.east.paas.project_configuration_filename' => 'space.paas.yaml',
    'teknoo.east.paas.root_dir' => dirname(__DIR__),

    'teknoo.east.paas.worker.time_limit' => env('SPACE_WORKER_TIME_LIMIT', '300'),

    'teknoo.east.paas.default_storage_provider' => env('SPACE_STORAGE_CLASS', 'space-nfs'),
    'teknoo.east.paas.default_storage_size' => env('SPACE_STORAGE_DEFAULT_SIZE', '2Gi'),

    'teknoo.east.paas.worker.tmp_dir' => get('teknoo.space.job_root'),

    'teknoo.east.paas.git.cloning.timeout' => env('SPACE_GIT_TIMEOUT', 240),

    'teknoo.east.paas.img_builder.cmd' => env('SPACE_IMG_BUILDER_CMD', 'buildah'),
    'teknoo.east.paas.img_builder.build.timeout' => env('SPACE_IMG_BUILDER_TIMEOUT', 10 * 60),
    'teknoo.east.paas.img_builder.build.platforms' => env('SPACE_IMG_BUILDER_PLATFORMS', 'linux/amd64'),

    'teknoo.east.paas.kubernetes.timeout' => env('SPACE_KUBERNETES_CLIENT_TIMEOUT', 10),
    'teknoo.east.paas.kubernetes.ssl.verify' => env('SPACE_KUBERNETES_CLIENT_VERIFY_SSL', true),

    'teknoo.east.paas.kubernetes.ingress.default_ingress_class' => env(
        'SPACE_KUBERNETES_INGRESS_DEFAULT_CLASS',
        'public'
    ),

    'teknoo.east.paas.kubernetes.ingress.backend_annotations_mapper' => static function (
        ContainerInterface $container
    ): callable {
        $providersList = $container->get('teknoo.space.kubernetes.ingress.providers-list');

        return static function (
            ?string $provider,
            bool $isHttpsBackend,
        ) use ($providersList): array {
            $providerTypeFound = '';
            if (!empty($provider)) {
                foreach ($providersList as $providerRegex => $providerType) {
                    if (preg_match($providerRegex, $provider)) {
                        $providerTypeFound = strtolower($providerType);

                        break;
                    }
                }
            }

            $key = match ($providerTypeFound) {
                'traefik', 'traefik1' => 'ingress.kubernetes.io/protocol',
                'traefik2' => 'traefik.ingress.kubernetes.io/router.entrypoints',
                'haproxy' => 'haproxy.org/server-ssl',
                'aws' => 'alb.ingress.kubernetes.io/backend-protocol',
                'gce' => 'cloud.google.com/app-protocols',
                default => 'nginx.ingress.kubernetes.io/backend-protocol'
            };

            if ('nginx.ingress.kubernetes.io/backend-protocol' === $key && !$isHttpsBackend) {
                return [];
            }

            return [
                $key => match ($providerTypeFound) {
                    'traefik', 'traefik1' => match ($isHttpsBackend) {
                        true => 'https',
                        false => 'http'
                    },
                    'traefik2' => match ($isHttpsBackend) {
                        true => 'websecure',
                        false => 'web'
                    },
                    'haproxy' => match ($isHttpsBackend) {
                        true => 'true',
                        false => 'false'
                    },
                    default => match ($isHttpsBackend) {
                        true => 'HTTPS',
                        false => 'HTTP'
                    },
                },
            ];
        };
    },

    // docker-compose driver tunables (spec §7); each is optional — the vendored DockerCompose/di.php guards
    // every one with $container->has(...) and falls back to its own default when the param is absent.
    'teknoo.east.paas.docker-compose.ansible.binary' => env('SPACE_DC_ANSIBLE_BINARY', 'ansible-playbook'),
    'teknoo.east.paas.docker-compose.timeout' => env('SPACE_DC_TIMEOUT', 900),
    'teknoo.east.paas.docker-compose.deploy_root' => env('SPACE_DC_DEPLOY_ROOT', '/opt/paas'),
    'teknoo.east.paas.docker-compose.network.driver' => env('SPACE_DC_NETWORK_DRIVER', 'bridge'),
    'teknoo.east.paas.docker-compose.traefik.container' => env('SPACE_DC_TRAEFIK_CONTAINER', 'traefik'),
    'teknoo.east.paas.docker-compose.traefik.dynamic_dir' => env(
        'SPACE_DC_TRAEFIK_DYNAMIC_DIR',
        '/etc/traefik/dynamic'
    ),
    'teknoo.east.paas.docker-compose.traefik.certs_dir' => env('SPACE_DC_TRAEFIK_CERTS_DIR', '/etc/traefik/certs'),
    'teknoo.east.paas.docker-compose.traefik.entrypoint.web' => env('SPACE_DC_TRAEFIK_ENTRYPOINT_WEB', 'web'),
    'teknoo.east.paas.docker-compose.traefik.entrypoint.websecure' => env(
        'SPACE_DC_TRAEFIK_ENTRYPOINT_WEBSECURE',
        'websecure'
    ),
    'teknoo.east.paas.docker-compose.https_backend.insecure_skip_verify' => env(
        'SPACE_DC_HTTPS_BACKEND_INSECURE_SKIP_VERIFY',
        false
    ),

    // Per-account private registry (docker-compose): a dedicated `registry` container provisioned over Ansible on
    // the external private network, exposed by the host's Traefik (websecure) as `<namespace>-registry.<host>`.
    // TLS between Traefik and the registry container optional.
    'teknoo.east.paas.docker-compose.registry.image' => env('SPACE_DC_REGISTRY_IMAGE', 'registry:2'),
    'teknoo.east.paas.docker-compose.registry.network' => env('SPACE_DC_REGISTRY_NETWORK', 'space-registry'),
    'teknoo.east.paas.docker-compose.registry.port' => env('SPACE_DC_REGISTRY_PORT', 5000),
    'teknoo.east.paas.docker-compose.registry.tls' => env('SPACE_DC_REGISTRY_TLS', false),
];

// The default_certresolver param is only declared when SPACE_DC_TRAEFIK_CERTRESOLVER is explicitly set, so the
// vendored DockerCompose/di.php keeps applying its own default when the operator did not configure a resolver.
if (!empty($_ENV['SPACE_DC_TRAEFIK_CERTRESOLVER'])) {
    $parameters['teknoo.east.paas.docker-compose.traefik.default_certresolver']
        = $_ENV['SPACE_DC_TRAEFIK_CERTRESOLVER'];
}

// Declare the per-project network `internal: true` (no egress from the containers, only reachable through Traefik;
// published host ports of public services are then unreachable). Off by default, like Kubernetes pods. Resolved
// here (not through env()) so "false" is a boolean false, not a non-empty string.
$parameters['teknoo.east.paas.docker-compose.network.internal'] = filter_var(
    $_ENV['SPACE_DC_NETWORK_INTERNAL'] ?? false,
    FILTER_VALIDATE_BOOL,
);

// The certs directory as seen by the Traefik process (path referenced by the generated dynamic files): the host
// directory `traefik.certs_dir`, bind-mounted at this path in the Traefik container. Defaults to the same path.
$parameters['teknoo.east.paas.docker-compose.traefik.certs_mount_dir'] = (string) (
    $_ENV['SPACE_DC_TRAEFIK_CERTS_MOUNT_DIR'] ?? ($_ENV['SPACE_DC_TRAEFIK_CERTS_DIR'] ?? '/etc/traefik/certs')
);

return $parameters;
