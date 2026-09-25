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

namespace Teknoo\Space\Infrastructures\AnsibleDockerCompose;

use DomainException;
use League\Flysystem\Filesystem;
use SensitiveParameter;
use Teknoo\East\Paas\Infrastructures\DockerCompose\Contracts\RunnerFactoryInterface;
use Teknoo\East\Paas\Object\ClusterCredentials;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Recipe\Promise\PromiseInterface;
use Throwable;

use function basename;
use function explode;
use function is_string;
use function parse_url;
use function uniqid;

use const PHP_URL_HOST;
use const PHP_URL_PATH;
use const PHP_URL_PORT;

/**
 * Run an Ansible playbook over SSH on a single Docker host, through the East PaaS docker-compose
 * `RunnerFactoryInterface`/`RunnerInterface` (no custom runner). Every playbook Space runs itself goes through here.
 *
 * The single-host inventory follows the format and the address parsing of the East PaaS deploy inventory
 * (`ssh://user@host:port`, `host:port` or `host`, group `docker_host`): the SSH user and the private key are applied
 * by the runner from the `ClusterCredentials`, never written in the inventory. It is written under the worker tmp
 * dir through a Flysystem adapter, named after the playbook, and removed once the playbook has run, whatever the
 * outcome. The result, or the error, is then forwarded to the caller's promise.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class PlaybookRunner
{
    public function __construct(
        private readonly RunnerFactoryInterface $runnerFactory,
        private readonly Filesystem $filesystem,
        private readonly string $tmpDir,
    ) {
    }

    private function renderInventory(string $address): ?string
    {
        $host = parse_url($address, PHP_URL_HOST);
        if (empty($host)) {
            //No scheme: parse_url returns the whole string as path for "host:port" / "host"
            $path = parse_url($address, PHP_URL_PATH);
            if (!is_string($path) || '' === $path) {
                $path = $address;
            }

            $parts = explode(':', $path, 2);
            $host = $parts[0];
            $port = $parts[1] ?? null;
        } else {
            $port = parse_url($address, PHP_URL_PORT);
        }

        if ('' === $host) {
            return null;
        }

        return "[docker_host]\n{$host} ansible_host={$host} ansible_port=" . ($port ?? 22) . "\n";
    }

    /**
     * @param array<string, mixed> $extraVars
     * @param PromiseInterface<array<mixed>|string, mixed> $promise
     */
    public function run(
        string $playbookPath,
        string $address,
        #[SensitiveParameter] ClusterCredentials $credentials,
        array $extraVars,
        PromiseInterface $promise,
    ): self {
        $inventory = $this->renderInventory($address);
        if (null === $inventory) {
            $promise->fail(new DomainException("Invalid SSH address '{$address}': unable to parse the host"));

            return $this;
        }

        $fileName = basename($playbookPath, '.yml') . '-inventory-' . uniqid() . '.ini';
        $this->filesystem->write($fileName, $inventory);

        /** @var Promise<array<string, mixed>|string, mixed, mixed> $runPromise */
        $runPromise = new Promise(
            onSuccess: function (array|string $result) use ($fileName, $promise): void {
                $this->filesystem->delete($fileName);

                $promise->success($result);
            },
            onFail: function (#[SensitiveParameter] Throwable $error) use ($fileName, $promise): void {
                $this->filesystem->delete($fileName);

                $promise->fail($error);
            },
        );

        //Only the factory is guarded: the runner routes its own failures to the promise, and a throwing caller's
        //callback must not fail a promise already succeeded
        try {
            $runner = ($this->runnerFactory)($address, $credentials);
        } catch (Throwable $error) {
            $runPromise->fail($error);

            return $this;
        }

        $runner->run(
            $playbookPath,
            $this->tmpDir . '/' . $fileName,
            $extraVars,
            $credentials,
            $runPromise,
        );

        return $this;
    }
}
