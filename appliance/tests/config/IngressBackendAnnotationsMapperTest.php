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

namespace Teknoo\Space\Tests\Unit\Config;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * Exercises the `teknoo.east.paas.kubernetes.ingress.backend_annotations_mapper` closure of
 * `appliance/config/di.variables.east.paas.php`. `config/` is outside the coverage scope, so this test
 * documents and guards its behaviour rather than adding coverage.
 *
 * The `traefik2` cases are the reason this test exists. That branch was the only provider type no
 * scenario covered, and it mapped the backend scheme onto
 * `traefik.ingress.kubernetes.io/router.entrypoints`, an annotation describing the *router*: every
 * ingress with a clear text backend was pinned to the `web` entrypoint, lost its HTTPS router and
 * answered 404 on 443, while those with `https-backend: true` lost their HTTP one.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
#[CoversNothing]
class IngressBackendAnnotationsMapperTest extends TestCase
{
    /**
     * @var array<string, string>
     */
    private const array PROVIDERS_LIST = [
        '#public#i' => 'traefik2',
        '#v3#i' => 'traefik3',
        '#legacy#i' => 'traefik',
        '#hap#i' => 'haproxy',
        '#aws#i' => 'aws',
        '#gce#i' => 'gce',
        '#nginx#i' => 'nginx',
    ];

    private function buildMapper(): callable
    {
        /** @var array<string, callable> $config */
        $config = require __DIR__ . '/../../config/di.variables.east.paas.php';

        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnCallback(
                static fn (string $id): mixed => match ($id) {
                    'teknoo.space.kubernetes.ingress.providers-list' => self::PROVIDERS_LIST,
                    default => null,
                }
            );

        return $config['teknoo.east.paas.kubernetes.ingress.backend_annotations_mapper']($container);
    }

    /**
     * @return array<string, array{0: string|null, 1: bool, 2: array<string, string>}>
     */
    public static function annotationsProvider(): array
    {
        return [
            'traefik v2/v3, clear text backend: nothing, the router must stay on every entrypoint' => [
                'public',
                false,
                [],
            ],
            'traefik v2/v3, https backend: nothing either, no ingress annotation can carry the scheme' => [
                'public',
                true,
                [],
            ],
            'traefik3 is an alias of traefik2, clear text backend' => [
                'v3',
                false,
                [],
            ],
            'traefik3 is an alias of traefik2, https backend' => [
                'v3',
                true,
                [],
            ],
            'traefik v1, https backend' => [
                'legacy',
                true,
                ['ingress.kubernetes.io/protocol' => 'https'],
            ],
            'traefik v1, clear text backend' => [
                'legacy',
                false,
                ['ingress.kubernetes.io/protocol' => 'http'],
            ],
            'haproxy, https backend' => [
                'hap',
                true,
                ['haproxy.org/server-ssl' => 'true'],
            ],
            'haproxy, clear text backend' => [
                'hap',
                false,
                ['haproxy.org/server-ssl' => 'false'],
            ],
            'aws' => [
                'aws',
                true,
                ['alb.ingress.kubernetes.io/backend-protocol' => 'HTTPS'],
            ],
            'gce' => [
                'gce',
                true,
                ['cloud.google.com/app-protocols' => 'HTTPS'],
            ],
            'nginx, https backend' => [
                'nginx',
                true,
                ['nginx.ingress.kubernetes.io/backend-protocol' => 'HTTPS'],
            ],
            'nginx, clear text backend' => [
                'nginx',
                false,
                [],
            ],
            'an unknown class falls back on nginx' => [
                'some-class',
                true,
                ['nginx.ingress.kubernetes.io/backend-protocol' => 'HTTPS'],
            ],
            'no class at all' => [
                null,
                false,
                [],
            ],
        ];
    }

    /**
     * @param array<string, string> $expected
     */
    #[DataProvider('annotationsProvider')]
    public function testBackendAnnotations(?string $provider, bool $isHttpsBackend, array $expected): void
    {
        $mapper = $this->buildMapper();

        self::assertSame($expected, $mapper($provider, $isHttpsBackend));
    }

    public function testRouterEntrypointsIsNeverWritten(): void
    {
        $mapper = $this->buildMapper();

        foreach (['public', 'v3', 'legacy', 'hap', 'aws', 'gce', 'nginx', 'some-class', null] as $provider) {
            foreach ([true, false] as $isHttpsBackend) {
                self::assertArrayNotHasKey(
                    'traefik.ingress.kubernetes.io/router.entrypoints',
                    $mapper($provider, $isHttpsBackend),
                );
            }
        }
    }
}
