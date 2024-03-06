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
use Teknoo\Space\Configuration\Exception\UnsupportedConfigurationException;

use function DI\env;
use function DI\get;
use function DI\factory;
use function dirname;
use function is_array;
use function is_file;
use function json_decode;

use const JSON_THROW_ON_ERROR;

return [
    //East PaaS Configuration
    'teknoo.east.paas.project_configuration_filename' => 'space.paas.yaml',
    'teknoo.east.paas.root_dir' => dirname(__DIR__),

    'teknoo.east.paas.worker.time_limit' => env('SPACE_WORKER_TIME_LIMIT', '300'),

    'teknoo.east.paas.default_storage_provider' => env('SPACE_STORAGE_CLASS', 'space-nfs'),
    'teknoo.east.paas.default_storage_size' => env('SPACE_STORAGE_DEFAULT_SIZE', '2Gi'),

    'teknoo.east.paas.worker.tmp_dir' => get('teknoo.space.job_root'),

    'teknoo.east.paas.git.cloning.timeout' => env('SPACE_GIT_TIMEOUT', 240),

    'teknoo.east.paas.composer.timeout' => env('SPACE_COMPOSER_TIMEOUT', 240),

    'teknoo.east.paas.symfony_console.timeout' => env('SPACE_SFCONSOLE_TIMEOUT', 240),

    'teknoo.east.paas.npm.timeout' => env('SPACE_NPM_TIMEOUT', 240),

    'teknoo.east.paas.pip.timeout' => env('SPACE_PIP_TIMEOUT', 240),

    'teknoo.east.paas.make.timeout' => env('SPACE_MAKE_TIMEOUT', 240),

    'teknoo.east.paas.img_builder.cmd' => env('SPACE_IMG_BUILDER_CMD', 'buildah'),
    'teknoo.east.paas.img_builder.build.timeout' => env('SPACE_IMG_BUILDER_TIMEOUT', 10 * 60),
    'teknoo.east.paas.img_builder.build.platforms' => env('SPACE_IMG_BUILDER_PLATFORMS', 'linux/amd64'),

    'teknoo.east.paas.kubernetes.timeout' => env('SPACE_KUBERNETES_CLIENT_TIMEOUT', 10),
    'teknoo.east.paas.kubernetes.ssl.verify' => env('SPACE_KUBERNETES_CLIENT_VERIFY_SSL', true),

    'teknoo.east.paas.kubernetes.ingress.default_ingress_class' => env(
        'SPACE_KUBERNETES_INGRESS_DEFAULT_CLASS',
        'public'
    ),
];
