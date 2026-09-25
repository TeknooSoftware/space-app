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

namespace Teknoo\Space\Tests\Unit\Infrastructures\AnsibleDockerCompose;

use DomainException;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Teknoo\East\Paas\Infrastructures\DockerCompose\Contracts\RunnerFactoryInterface;
use Teknoo\East\Paas\Infrastructures\DockerCompose\Contracts\RunnerInterface;
use Teknoo\East\Paas\Object\ClusterCredentials;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Infrastructures\AnsibleDockerCompose\PlaybookRunner;
use Throwable;

use function basename;
use function iterator_to_array;
use function preg_match;
use function str_starts_with;
use function strlen;
use function substr;

/**
 * Class PlaybookRunnerTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(PlaybookRunner::class)]
class PlaybookRunnerTest extends TestCase
{
    private const string TMP_DIR = '/tmp/worker';

    private const string PLAYBOOK = '/path/to/registries-login.yml';

    private const string ADDRESS = 'ssh://deployer@host.example.com:2222';

    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem(new InMemoryFilesystemAdapter());
    }

    private function buildCredentials(): ClusterCredentials
    {
        return new ClusterCredentials(
            caCertificate: 'known-hosts',
            clientKey: '-----BEGIN OPENSSH PRIVATE KEY-----KEY',
            username: 'deployer',
        );
    }

    /**
     * @param array<int, mixed> $captured
     */
    private function buildRunner(array &$captured, ?Throwable $failure = null): RunnerInterface
    {
        $runner = $this->createMock(RunnerInterface::class);
        $runner->expects($this->once())
            ->method('run')
            ->willReturnCallback(
                function (
                    string $playbook,
                    string $inventoryPath,
                    array $extraVars,
                    ?ClusterCredentials $credentials,
                    PromiseInterface $promise,
                ) use (
                    &$captured,
                    $runner,
                    $failure,
                ): RunnerInterface {
                    $this->assertTrue(str_starts_with($inventoryPath, self::TMP_DIR . '/'));
                    $inventory = $this->filesystem->read(substr($inventoryPath, 1 + strlen(self::TMP_DIR)));

                    $captured = [$playbook, $inventoryPath, $inventory, $extraVars, $credentials];

                    if (null === $failure) {
                        $promise->success('PLAY RECAP');
                    } else {
                        $promise->fail($failure);
                    }

                    return $runner;
                }
            );

        return $runner;
    }

    private function buildFactory(string $address, RunnerInterface $runner): RunnerFactoryInterface
    {
        $factory = $this->createMock(RunnerFactoryInterface::class);
        $factory->expects($this->once())
            ->method('__invoke')
            ->with($address, $this->isInstanceOf(ClusterCredentials::class))
            ->willReturn($runner);

        return $factory;
    }

    /**
     * @param array{success?: mixed, fail?: Throwable} $outcome
     * @return PromiseInterface<array<mixed>|string, mixed>
     */
    private function buildPromise(array &$outcome): PromiseInterface
    {
        $promise = $this->createStub(PromiseInterface::class);
        $promise->method('success')->willReturnCallback(
            static function (mixed $result) use (&$outcome, $promise): PromiseInterface {
                $outcome['success'] = $result;

                return $promise;
            }
        );
        $promise->method('fail')->willReturnCallback(
            static function (Throwable $error) use (&$outcome, $promise): PromiseInterface {
                $outcome['fail'] = $error;

                return $promise;
            }
        );

        return $promise;
    }

    private function assertNoInventoryLeft(): void
    {
        $this->assertSame([], iterator_to_array($this->filesystem->listContents('', true)));
    }

    public function testRunRunsThePlaybookAndForwardsTheResult(): void
    {
        $captured = [];
        $outcome = [];
        $credentials = $this->buildCredentials();

        $runner = new PlaybookRunner(
            $this->buildFactory(self::ADDRESS, $this->buildRunner($captured)),
            $this->filesystem,
            self::TMP_DIR,
        );

        $this->assertInstanceOf(
            PlaybookRunner::class,
            $runner->run(
                self::PLAYBOOK,
                self::ADDRESS,
                $credentials,
                ['foo' => 'bar'],
                $this->buildPromise($outcome),
            ),
        );

        [$playbook, $inventoryPath, $inventory, $extraVars, $capturedCredentials] = $captured;
        $this->assertSame(self::PLAYBOOK, $playbook);
        $this->assertSame(1, preg_match('#^registries-login-inventory-.+\.ini$#', basename($inventoryPath)));
        $this->assertSame(
            "[docker_host]\nhost.example.com ansible_host=host.example.com ansible_port=2222\n",
            $inventory,
        );
        $this->assertSame(['foo' => 'bar'], $extraVars);
        $this->assertSame($credentials, $capturedCredentials);

        $this->assertSame(['success' => 'PLAY RECAP'], $outcome);
        $this->assertNoInventoryLeft();
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function addressesProvider(): iterable
    {
        yield 'ssh url with user and port' => [
            'ssh://deployer@host.example.com:2222',
            "[docker_host]\nhost.example.com ansible_host=host.example.com ansible_port=2222\n",
        ];

        yield 'ssh url without port' => [
            'ssh://host.example.com',
            "[docker_host]\nhost.example.com ansible_host=host.example.com ansible_port=22\n",
        ];

        yield 'bare host' => [
            '192.168.122.150',
            "[docker_host]\n192.168.122.150 ansible_host=192.168.122.150 ansible_port=22\n",
        ];

        yield 'host and port' => [
            '192.168.122.150:2200',
            "[docker_host]\n192.168.122.150 ansible_host=192.168.122.150 ansible_port=2200\n",
        ];
    }

    #[DataProvider('addressesProvider')]
    public function testRunWritesTheInventoryOfTheHost(string $address, string $expected): void
    {
        $captured = [];
        $outcome = [];

        new PlaybookRunner(
            $this->buildFactory($address, $this->buildRunner($captured)),
            $this->filesystem,
            self::TMP_DIR,
        )->run(self::PLAYBOOK, $address, $this->buildCredentials(), [], $this->buildPromise($outcome));

        $this->assertSame($expected, $captured[2]);
    }

    public function testRunFailsOnAnUnparsableAddress(): void
    {
        $factory = $this->createMock(RunnerFactoryInterface::class);
        $factory->expects($this->never())->method('__invoke');

        $outcome = [];

        new PlaybookRunner($factory, $this->filesystem, self::TMP_DIR)
            ->run(self::PLAYBOOK, ':22', $this->buildCredentials(), [], $this->buildPromise($outcome));

        $this->assertArrayNotHasKey('success', $outcome);
        $this->assertInstanceOf(DomainException::class, $outcome['fail'] ?? null);
        $this->assertNoInventoryLeft();
    }

    public function testRunForwardsThePlaybookFailureAndRemovesTheInventory(): void
    {
        $error = new RuntimeException('playbook failed');

        $captured = [];
        $outcome = [];

        new PlaybookRunner(
            $this->buildFactory(self::ADDRESS, $this->buildRunner($captured, $error)),
            $this->filesystem,
            self::TMP_DIR,
        )->run(self::PLAYBOOK, self::ADDRESS, $this->buildCredentials(), [], $this->buildPromise($outcome));

        $this->assertSame(['fail' => $error], $outcome);
        $this->assertNoInventoryLeft();
    }

    public function testRunForwardsAFactoryFailureAndRemovesTheInventory(): void
    {
        $error = new RuntimeException('unable to write the private key');

        $factory = $this->createMock(RunnerFactoryInterface::class);
        $factory->expects($this->once())
            ->method('__invoke')
            ->willThrowException($error);

        $outcome = [];

        new PlaybookRunner($factory, $this->filesystem, self::TMP_DIR)
            ->run(self::PLAYBOOK, self::ADDRESS, $this->buildCredentials(), [], $this->buildPromise($outcome));

        $this->assertSame(['fail' => $error], $outcome);
        $this->assertNoInventoryLeft();
    }
}
