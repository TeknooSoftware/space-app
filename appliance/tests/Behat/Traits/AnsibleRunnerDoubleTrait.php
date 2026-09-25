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

namespace Teknoo\Space\Tests\Behat\Traits;

use League\Flysystem\Filesystem;
use PHPUnit\Framework\MockObject\Generator\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\AnyInvokedCount as AnyInvokedCountMatcher;
use Symfony\Component\Process\Process;
use Teknoo\East\Paas\Infrastructures\DockerCompose\Contracts\RunnerInterface;
use Teknoo\East\Paas\Infrastructures\DockerCompose\RunnerFactory;
use Teknoo\East\Paas\Infrastructures\DockerCompose\SymfonyProcessRunner;

use function count;

/**
 * Doubles of the Ansible runner layer, shared by every Behat context running a playbook: the East PaaS
 * `RunnerFactory` and `SymfonyProcessRunner` are the real ones, only the Symfony `Process` is mocked, so nothing
 * is executed while each `ansible-playbook` invocation becomes assertable.
 *
 * Helpers only, without any step definition: a context of an extension can use it next to the core contexts
 * without declaring a step twice.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
trait AnsibleRunnerDoubleTrait
{
    /**
     * The real factory, on the given Flysystem (in memory, so the private key never touches the disk) with
     * `tmpDir: ''` and deterministic names for the files it materializes, making the "--private-key" argument and
     * the known_hosts path assertable. The factory materializes the private key first, then the known_hosts built
     * from the credentials' CA certificate field: the names are handed out in turn, once per file and per run - a
     * single name would make the known_hosts overwrite the private key.
     *
     * @param list<string> $materializedFileNames
     * @param callable(array<int, string>, ?float): Process $processFactory
     */
    private function buildAnsibleRunnerFactory(
        Filesystem $filesystem,
        float $timeout,
        array $materializedFileNames,
        callable $processFactory,
    ): RunnerFactory {
        $materializedFiles = 0;

        return new RunnerFactory(
            filesystem: $filesystem,
            tmpDir: '',
            playbookBinary: 'ansible-playbook',
            timeout: $timeout,
            keyFileNameFactory: static function () use (&$materializedFiles, $materializedFileNames): string {
                return $materializedFileNames[$materializedFiles++ % count($materializedFileNames)];
            },
            //Only overridden to inject the mocked process factory: the runner itself is the real one.
            runnerBuilder: static fn (
                string $playbookBinary,
                ?float $runnerTimeout,
                ?string $sshUser,
                ?string $privateKeyFile,
                ?string $knownHostsFile = null,
            ): RunnerInterface => new SymfonyProcessRunner(
                playbookBinary: $playbookBinary,
                timeout: $runnerTimeout,
                sshUser: $sshUser,
                privateKeyFile: $privateKeyFile,
                processFactory: $processFactory,
                knownHostsFile: $knownHostsFile,
            ),
        );
    }

    /**
     * Record the `ansible-playbook` invocation in `$runs` and answer it with a mocked `Process`. The real
     * `SymfonyProcessRunner` sets the non-interactive Ansible environment on the process: it is kept with the run
     * so it can be asserted. A failure is expressed where a real one surfaces: `SymfonyProcessRunner` turns
     * `!isSuccessful()` into a RuntimeException carrying the outputs.
     *
     * Positional arguments: `Generator` is annotated `@no-named-arguments`. After $methods, $arguments and
     * $mockClassName come $callOriginalConstructor and $callOriginalClone, both disabled so no real process is
     * ever built.
     *
     * @param list<array{command: array<int, string>, timeout: float|null, env: array<string, string>}> $runs
     * @param array<int, string> $command
     */
    private function buildAnsibleProcessDouble(
        array &$runs,
        array $command,
        ?float $timeout,
        bool $successful,
        string $output,
        string $errorOutput = '',
    ): Process {
        $runIndex = count($runs);
        $runs[] = ['command' => $command, 'timeout' => $timeout, 'env' => []];

        /** @var MockObject&Process $process */
        $process = new Generator()->testDouble(Process::class, true, [], [], '', false, false);

        $process->expects(new AnyInvokedCountMatcher())->method('run');
        $process->method('setEnv')->willReturnCallback(
            static function (array $env) use (&$runs, $runIndex, $process): Process {
                /** @var array<string, string> $env */
                $runs[$runIndex]['env'] = $env;

                return $process;
            },
        );
        $process->method('isSuccessful')->willReturn($successful);
        $process->method('getOutput')->willReturn($output);
        $process->method('getErrorOutput')->willReturn($errorOutput);

        return $process;
    }
}
