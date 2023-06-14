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

use ArrayObject;

use function DI\env;
use function DI\get;
use function dirname;
use function is_array;
use function is_file;
use function json_decode;

$loadFromEnv = static function (mixed $default, string $jsonKey, string $fileKey): callable
{
    return static function () use ($default, $jsonKey, $fileKey): mixed {
        $value = $default;

        if (!empty($_ENV[$jsonKey])) {
            $value = json_decode(
                json: $_ENV[$jsonKey],
                associative: true,
            );
        }

        if (
            !empty($_ENV[$fileKey])
            && is_file($file = $_ENV[$fileKey])
        ) {
            $value = require $file;
        }

        if (is_array($value)) {
            $value = new ArrayObject($value);
        }

        return $value;
    };
};

return [
    //East PaaS Configuration
    'teknoo.east.paas.project_configuration_filename' => '.paas.yaml',
    'teknoo.east.paas.root_dir' => dirname(__DIR__),

    'teknoo.east.paas.worker.time_limit' => env('SPACE_WORKER_TIME_LIMIT', '300'),

    'teknoo.east.paas.default_storage_provider' => env('SPACE_STORAGE_CLASS', 'space-nfs'),
    'teknoo.east.paas.default_storage_size' => env('SPACE_STORAGE_DEFAULT_SIZE', '2Gi'),

    'teknoo.east.paas.worker.tmp_dir' => get('teknoo.space.job_root'),

    'teknoo.east.paas.git.cloning.timeout' => env('SPACE_GIT_TIMEOUT', 240),

    'teknoo.east.paas.composer.path' => env('SPACE_COMPOSER_PATH', 'composer'),
    'teknoo.east.paas.composer.timeout' => env('SPACE_COMPOSER_TIMEOUT', 240),
    'teknoo.east.paas.npm.path' => env('SPACE_NPM_PATH', 'npm'),
    'teknoo.east.paas.npm.timeout' => env('SPACE_NPM_TIMEOUT', 240),
    'teknoo.east.paas.pip.path' => env('SPACE_PIP_PATH', 'pip'),
    'teknoo.east.paas.pip.timeout' => env('SPACE_PIP_TIMEOUT', 240),
    'teknoo.east.paas.make.path' => env('SPACE_MAKE_PATH', 'make'),
    'teknoo.east.paas.make.timeout' => env('SPACE_MAKE_TIMEOUT', 240),

    'teknoo.east.paas.img_builder.cmd' => env('SPACE_IMG_BUILDER_CMD', 'buildah'),
    'teknoo.east.paas.img_builder.build.timeout' => env('SPACE_IMG_BUILDER_TIMEOUT', 10*60),
    'teknoo.east.paas.img_builder.build.platforms' => env('SPACE_IMG_BUILDER_PLATFORMS', 'linux/amd64'),

    'teknoo.east.paas.kubernetes.timeout' => env('SPACE_KUBERNETES_CLIENT_TIMEOUT', 10),
    'teknoo.east.paas.kubernetes.ssl.verify' => env('SPACE_KUBERNETES_CLIENT_VERIFY_SSL', true),

    'teknoo.east.paas.kubernetes.ingress.default_ingress_class' => env(
        'SPACE_KUBERNETES_INGRESS_DEFAULT_CLASS',
        'public'
    ),
    'teknoo.east.paas.kubernetes.ingress.default_annotations' => $loadFromEnv(
        [
            'cert-manager.io/cluster-issuer' => 'lets-encrypt',
        ],
        'SPACE_KUBERNETES_INGRESS_DEFAULT_ANNOTATIONS_JSON',
        'SPACE_KUBERNETES_INGRESS_DEFAULT_ANNOTATIONS_FILE',
    ),

    'teknoo.east.paas.compilation.containers_images_library' => $loadFromEnv(
        [],
        'SPACE_PAAS_IMAGE_LIBRARY_JSON',
        'SPACE_PAAS_IMAGE_LIBRARY_FILE',
    ),

    'teknoo.east.paas.compilation.global_variables' => $loadFromEnv(
        [],
        'SPACE_PAAS_GLOBAL_VARIABLES_JSON',
        'SPACE_PAAS_GLOBAL_VARIABLES_FILE',
    ),

    'teknoo.east.paas.compilation.pods_extends.library' => $loadFromEnv(
        [],
        'SPACE_PAAS_COMPILATION_PODS_EXTENDS_LIBRARY_JSON',
        'SPACE_PAAS_COMPILATION_PODS_EXTENDS_LIBRARY_FILE',
    ),

    'teknoo.east.paas.compilation.containers_extends.library' => $loadFromEnv(
        [],
        'SPACE_PAAS_COMPILATION_CONTAINERS_EXTENDS_LIBRARY_JSON',
        'SPACE_PAAS_COMPILATION_CONTAINERS_EXTENDS_LIBRARY_FILE',
    ),

    'teknoo.east.paas.compilation.services_extends.library' => $loadFromEnv(
        [],
        'SPACE_PAAS_COMPILATION_SERVICES_EXTENDS_LIBRARY_JSON',
        'SPACE_PAAS_COMPILATION_SERVICES_EXTENDS_LIBRARY_FILE',
    ),

    'teknoo.east.paas.compilation.ingresses_extends.library' => $loadFromEnv(
        [],
        'SPACE_PAAS_COMPILATION_INGRESSES_EXTENDS_LIBRARY_JSON',
        'SPACE_PAAS_COMPILATION_INGRESSES_EXTENDS_LIBRARY_FILE',
    ),
];
