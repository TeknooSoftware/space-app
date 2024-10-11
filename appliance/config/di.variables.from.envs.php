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
use Psr\Container\ContainerInterface;
use Teknoo\Space\Configuration\Exception\UnsupportedConfigurationException;

use function DI\factory;
use function array_merge;
use function is_array;
use function is_file;
use function json_decode;

use const JSON_THROW_ON_ERROR;

$loadFromEnv = static function (
    ContainerInterface $container,
    mixed $default,
    string $jsonKey,
    string $fileKey,
    ?string $parameterName = null,
    bool $prependFromContainer = false,
): ArrayObject {
    $value = null;

    if (!empty($_ENV[$jsonKey]) && !empty($_ENV[$fileKey])) {
        throw new UnsupportedConfigurationException(
            "{$jsonKey} and {$fileKey} can not be filled in same time",
        );
    }

    $valuesFromContainer = [];
    if (
        !empty($parameterName)
        && $container->has($parameterName)
    ) {
        $valuesFromContainer = $container->get($parameterName);
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

    if (!empty($valuesFromContainer)) {
        if ($prependFromContainer && is_array($value)) {
            $value = array_merge($valuesFromContainer, $value);
        } elseif (empty($value)) {
            $value = $valuesFromContainer;
        }
    }

    $value ??= $default;

    if (is_array($value)) {
        $value = new ArrayObject($value);
    }

    return $value;
};

return [
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
        )
        ->parameter(
            'parameterName',
            'teknoo.east.paas.kubernetes.ingress.default_annotations.value',
        )
        ->parameter(
            'prependFromContainer',
            true,
        ),

    'teknoo.space.hooks_collection.definitions' => factory($loadFromEnv)
        ->parameter(
            'default',
            [],
        )
        ->parameter(
            'jsonKey',
            'SPACE_HOOKS_COLLECTION_JSON',
        )
        ->parameter(
            'fileKey',
            'SPACE_HOOKS_COLLECTION_FILE',
        )
        ->parameter(
            'parameterName',
            'teknoo.space.hooks_collection.definitions.value',
        )
        ->parameter(
            'prependFromContainer',
            true,
        ),

    'teknoo.space.clusters_catalog.definitions' => factory($loadFromEnv)
        ->parameter(
            'default',
            [],
        )
        ->parameter(
            'jsonKey',
            'SPACE_CLUSTER_CATALOG_JSON',
        )
        ->parameter(
            'fileKey',
            'SPACE_CLUSTER_CATALOG_FILE',
        )
        ->parameter(
            'parameterName',
            'teknoo.space.clusters_catalog.definitions.value',
        )
        ->parameter(
            'prependFromContainer',
            true,
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
        )
        ->parameter(
            'parameterName',
            'teknoo.east.paas.compilation.containers_images_library.value',
        )
        ->parameter(
            'prependFromContainer',
            true,
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
        )
        ->parameter(
            'parameterName',
            'teknoo.east.paas.compilation.global_variables.value',
        )
        ->parameter(
            'prependFromContainer',
            true,
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
        )
        ->parameter(
            'parameterName',
            'teknoo.east.paas.compilation.pods_extends.library.value',
        )
        ->parameter(
            'prependFromContainer',
            true,
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
        )
        ->parameter(
            'parameterName',
            'teknoo.east.paas.compilation.containers_extends.library.value',
        )
        ->parameter(
            'prependFromContainer',
            true,
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
        )
        ->parameter(
            'parameterName',
            'teknoo.east.paas.compilation.services_extends.library.value',
        )
        ->parameter(
            'prependFromContainer',
            true,
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
        )
        ->parameter(
            'parameterName',
            'teknoo.east.paas.compilation.ingresses_extends.library.value',
        )
        ->parameter(
            'prependFromContainer',
            true,
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
        )
        ->parameter(
            'parameterName',
            'teknoo.space.subscription_plan_catalog.definitions.value',
        )
        ->parameter(
            'prependFromContainer',
            true,
        ),
];
