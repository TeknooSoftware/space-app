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

$loadFromEnv = static function (
    mixed $default,
    string $jsonKey,
    string $fileKey,
): ArrayObject {
    $value = null;

    if (!empty($_ENV[$jsonKey]) && !empty($_ENV[$fileKey])) {
        throw new UnsupportedConfigurationException(
            "{$jsonKey} and {$fileKey} can not be filled in same time",
        );
    }

    if (!empty($_ENV[$jsonKey])) {
        $value = json_decode(
            json: $_ENV[$jsonKey],
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );
    } elseif (
        !empty($_ENV[$fileKey])
        && is_file($file = $_ENV[$fileKey])
    ) {
        $value = require $file;
    }

    $value ??= $default;

    if (is_array($value)) {
        $value = new ArrayObject($value);
    }

    return $value;
};

return [
    //East PaaS Configuration
    'teknoo.east.paas.composer.path' => factory($loadFromEnv)
        ->parameter(
            'default',
            ['composer'],
        )
        ->parameter(
            'jsonKey',
            'SPACE_COMPOSER_PATH_JSON',
        )
        ->parameter(
            'fileKey',
            'SPACE_COMPOSER_PATH_FILE',
        ),

    'teknoo.east.paas.symfony_console.path' => factory($loadFromEnv)
        ->parameter(
            'default',
            ['${PWD}/bin/console'],
        )
        ->parameter(
            'jsonKey',
            'SPACE_SFCONSOLE_PATH_JSON',
        )
        ->parameter(
            'fileKey',
            'SPACE_SFCONSOLE_PATH_FILE',
        ),

    'teknoo.east.paas.npm.path' => factory($loadFromEnv)
        ->parameter(
            'default',
            ['npm'],
        )
        ->parameter(
            'jsonKey',
            'SPACE_NPM_PATH_JSON',
        )
        ->parameter(
            'fileKey',
            'SPACE_NPM_PATH_FILE',
        ),

    'teknoo.east.paas.pip.path' => factory($loadFromEnv)
        ->parameter(
            'default',
            ['pip'],
        )
        ->parameter(
            'jsonKey',
            'SPACE_PIP_PATH_JSON',
        )
        ->parameter(
            'fileKey',
            'SPACE_PIP_PATH_FILE',
        ),

    'teknoo.east.paas.make.path' => factory($loadFromEnv)
        ->parameter(
            'default',
            ['make'],
        )
        ->parameter(
            'jsonKey',
            'SPACE_MAKE_PATH_JSON',
        )
        ->parameter(
            'fileKey',
            'SPACE_MAKE_PATH_FILE',
        ),

    'teknoo.east.paas.kubernetes.ingress.default_annotations' => factory($loadFromEnv)
        ->parameter(
            'default',
            [
                'cert-manager.io/cluster-issuer' => 'lets-encrypt',
            ],
        )
        ->parameter(
            'jsonKey',
            'SPACE_KUBERNETES_INGRESS_DEFAULT_ANNOTATIONS_JSON',
        )
        ->parameter(
            'fileKey',
            'SPACE_KUBERNETES_INGRESS_DEFAULT_ANNOTATIONS_FILE',
        ),

    'teknoo.space.clusters_catalog.definitions' => factory($loadFromEnv)
        ->parameter(
            'default',
            [],
        )
        ->parameter(
            'jsonKey',
            'SPACE_KUBERNETES_CLUSTER_CATALOG_JSON',
        )
        ->parameter(
            'fileKey',
            'SPACE_KUBERNETES_CLUSTER_CATALOG_FILE',
        ),

    'teknoo.east.paas.compilation.containers_images_library' => factory($loadFromEnv)
        ->parameter(
            'default',
            [],
        )
        ->parameter(
            'jsonKey',
            'SPACE_PAAS_IMAGE_LIBRARY_JSON',
        )
        ->parameter(
            'fileKey',
            'SPACE_PAAS_IMAGE_LIBRARY_FILE',
        ),

    'teknoo.east.paas.compilation.global_variables' => factory($loadFromEnv)
        ->parameter(
            'default',
            [],
        )
        ->parameter(
            'jsonKey',
            'SPACE_PAAS_GLOBAL_VARIABLES_JSON',
        )
        ->parameter(
            'fileKey',
            'SPACE_PAAS_GLOBAL_VARIABLES_FILE',
        ),

    'teknoo.east.paas.compilation.pods_extends.library' => factory($loadFromEnv)
        ->parameter(
            'default',
            [],
        )
        ->parameter(
            'jsonKey',
            'SPACE_PAAS_COMPILATION_PODS_EXTENDS_LIBRARY_JSON',
        )
        ->parameter(
            'fileKey',
            'SPACE_PAAS_COMPILATION_PODS_EXTENDS_LIBRARY_FILE',
        ),

    'teknoo.east.paas.compilation.containers_extends.library' => factory($loadFromEnv)
        ->parameter(
            'default',
            [],
        )
        ->parameter(
            'jsonKey',
            'SPACE_PAAS_COMPILATION_CONTAINERS_EXTENDS_LIBRARY_JSON',
        )
        ->parameter(
            'fileKey',
            'SPACE_PAAS_COMPILATION_CONTAINERS_EXTENDS_LIBRARY_FILE',
        ),

    'teknoo.east.paas.compilation.services_extends.library' => factory($loadFromEnv)
        ->parameter(
            'default',
            [],
        )
        ->parameter(
            'jsonKey',
            'SPACE_PAAS_COMPILATION_SERVICES_EXTENDS_LIBRARY_JSON',
        )
        ->parameter(
            'fileKey',
            'SPACE_PAAS_COMPILATION_SERVICES_EXTENDS_LIBRARY_FILE',
        ),

    'teknoo.east.paas.compilation.ingresses_extends.library' => factory($loadFromEnv)
        ->parameter(
            'default',
            [],
        )
        ->parameter(
            'jsonKey',
            'SPACE_PAAS_COMPILATION_INGRESSES_EXTENDS_LIBRARY_JSON',
        )
        ->parameter(
            'fileKey',
            'SPACE_PAAS_COMPILATION_INGRESSES_EXTENDS_LIBRARY_FILE',
        ),

    'teknoo.space.subscription_plan_catalog.definitions' => factory($loadFromEnv)
        ->parameter(
            'default',
            [],
        )
        ->parameter(
            'jsonKey',
            'SPACE_SUBSCRIPTION_PLAN_CATALOG_JSON',
        )
        ->parameter(
            'fileKey',
            'SPACE_SUBSCRIPTION_PLAN_CATALOG_FILE',
        ),
];
