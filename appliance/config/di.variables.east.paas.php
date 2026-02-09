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
use function preg_match;
use function strtolower;

return [
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
                'traefik2' => 'traefik.ingress.kubernetes.io/service.serversscheme',
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
                    'traefik', 'traefik1', 'traefik2' => match ($isHttpsBackend) {
                        true => 'https',
                        false => 'http'
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
];
