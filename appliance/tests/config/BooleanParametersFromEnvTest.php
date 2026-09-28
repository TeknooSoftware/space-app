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

use function array_key_exists;

/**
 * Exercises the boolean parameters of `appliance/config/di.variables.east.paas.php` filled from an env var.
 * `config/` is outside the coverage scope, so this test documents and guards their behaviour rather than adding
 * coverage.
 *
 * Their consumers (East PaaS and Space) cast them with `(bool)`: resolved through `DI\env()`, the variable would reach
 * them as a string and `"false"` would be read as true. That silently allowed a cluster's token to designate a file of
 * the worker, skipped the TLS verification of HTTPS backends, or enabled the registry TLS. Every value which is not an
 * explicit boolean true must give `false`.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
#[CoversNothing]
class BooleanParametersFromEnvTest extends TestCase
{
    /**
     * @var array<string, string>
     */
    private const array PARAMETERS = [
        'SPACE_KUBERNETES_CLIENT_ALLOW_TOKEN_FILE' => 'teknoo.east.paas.kubernetes.token.allow_file',
        'SPACE_DC_NETWORK_INTERNAL' => 'teknoo.east.paas.docker-compose.network.internal',
        'SPACE_DC_HTTPS_BACKEND_INSECURE_SKIP_VERIFY'
            => 'teknoo.east.paas.docker-compose.https_backend.insecure_skip_verify',
        'SPACE_DC_REGISTRY_TLS' => 'teknoo.east.paas.docker-compose.registry.tls',
    ];

    /**
     * @var array<string, bool>
     */
    private const array VALUES = [
        '' => false,
        '0' => false,
        'false' => false,
        'FALSE' => false,
        'off' => false,
        'no' => false,
        'maybe' => false,
        '1' => true,
        'true' => true,
        'TRUE' => true,
        'on' => true,
        'yes' => true,
    ];

    /**
     * @var array<string, mixed>
     */
    private array $previousValues = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->previousValues = [];
        foreach (self::PARAMETERS as $envName => $parameterName) {
            if (array_key_exists($envName, $_ENV)) {
                $this->previousValues[$envName] = $_ENV[$envName];
            }
        }
    }

    protected function tearDown(): void
    {
        foreach (self::PARAMETERS as $envName => $parameterName) {
            unset($_ENV[$envName]);
            if (array_key_exists($envName, $this->previousValues)) {
                $_ENV[$envName] = $this->previousValues[$envName];
            }
        }

        parent::tearDown();
    }

    private function loadParameter(string $parameterName): mixed
    {
        /** @var array<string, mixed> $config */
        $config = require __DIR__ . '/../../config/di.variables.east.paas.php';

        return $config[$parameterName] ?? null;
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function parametersProvider(): array
    {
        $cases = [];
        foreach (self::PARAMETERS as $envName => $parameterName) {
            $cases[$envName] = [$envName, $parameterName];
        }

        return $cases;
    }

    #[DataProvider('parametersProvider')]
    public function testDisabledWhenTheVariableIsNotDefined(string $envName, string $parameterName): void
    {
        unset($_ENV[$envName]);

        self::assertFalse($this->loadParameter($parameterName));
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string, 3: bool}>
     */
    public static function valuesProvider(): array
    {
        $cases = [];
        foreach (self::PARAMETERS as $envName => $parameterName) {
            foreach (self::VALUES as $value => $expected) {
                $cases["{$envName}={$value}"] = [$envName, $parameterName, (string) $value, $expected];
            }

            //An unexpanded variable of the php-fpm pool whitelist
            $cases["{$envName} unexpanded"] = [$envName, $parameterName, '$' . $envName, false];
        }

        return $cases;
    }

    #[DataProvider('valuesProvider')]
    public function testParameterFromTheVariable(
        string $envName,
        string $parameterName,
        string $value,
        bool $expected,
    ): void {
        $_ENV[$envName] = $value;

        self::assertSame($expected, $this->loadParameter($parameterName));
    }
}
