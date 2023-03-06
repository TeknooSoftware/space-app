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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Infrastructures\Kubernetes\Transcriber;

use Teknoo\East\Paas\Compilation\CompiledDeployment\Expose\Ingress;
use Teknoo\East\Paas\Infrastructures\Kubernetes\Transcriber\IngressTranscriber as BaseTranscriber;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class IngressTranscriber extends BaseTranscriber
{
    protected static function writeSpec(
        Ingress $ingress,
        string $namespace,
        ?string $defaultIngressClass,
        ?string $defaultIngressService,
        ?int $defaultIngressPort,
        array $defaultIngressAnnotations,
        callable $prefixer,
    ): array {
        $specs = parent::writeSpec(
            ingress: $ingress,
            namespace: $namespace,
            defaultIngressClass: $defaultIngressClass,
            defaultIngressService: $defaultIngressService,
            defaultIngressPort: $defaultIngressPort,
            defaultIngressAnnotations: $defaultIngressAnnotations,
            prefixer: $prefixer,
        );

        if (
            isset($specs['spec']['tls'])
            && !empty($specs['metadata']['annotations']['cert-manager.io/cluster-issuer'])
        ) {
            unset($specs['metadata']['annotations']['cert-manager.io/cluster-issuer']);
        }

        return $specs;
    }
}
