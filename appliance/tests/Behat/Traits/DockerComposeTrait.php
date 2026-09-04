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

use Behat\Step\Given;
use Behat\Step\Then;
use FilesystemIterator;
use JsonException;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\Generator\Generator;
use PHPUnit\Framework\MockObject\Rule\AnyInvokedCount as AnyInvokedCountMatcher;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Process\Process;
use Teknoo\East\Paas\Cluster\Directory;
use Teknoo\East\Paas\Infrastructures\DockerCompose\Contracts\RunnerInterface;
use Teknoo\East\Paas\Infrastructures\DockerCompose\Contracts\Transcriber\TranscriberCollectionInterface;
use Teknoo\East\Paas\Infrastructures\DockerCompose\Driver as DockerComposeDriver;
use Teknoo\East\Paas\Infrastructures\DockerCompose\RunnerFactory;
use Teknoo\East\Paas\Infrastructures\DockerCompose\SymfonyProcessRunner;

use function array_filter;
use function array_map;
use function array_values;
use function basename;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function getenv;
use function is_dir;
use function json_encode;
use function ksort;
use function mkdir;
use function pathinfo;
use function preg_match;
use function preg_replace;
use function reset;
use function str_ends_with;
use function strlen;
use function strtolower;
use function substr;
use function uniqid;

use const JSON_THROW_ON_ERROR;
use const PATHINFO_FILENAME;

/**
 * Behat steps exercising a docker-compose deployment end-to-end, modelled on the East PaaS docker-compose
 * Behat test (`vendor/teknoo/east-paas/tests/behat/FeatureContext.php::aDockerComposeOrchestrator`).
 *
 * The real `DockerCompose\Driver` runs against two in-memory Flysystem filesystems (never touching the disk),
 * and the whole runner layer above the process is the real one too: the real `RunnerFactory` resolves the SSH
 * login user and materializes the SSH private key into that in-memory workspace, and the real
 * `SymfonyProcessRunner` builds the `ansible-playbook` command line. Only the lowest seam - the Symfony
 * `Process` - is mocked, so nothing is ever executed while the command line, the rendered inventory, the
 * resolved SSH user and the materialized private key all become assertable (see `assertAnsibleRun()`).
 *
 * The artifacts the Driver produced (compose specification, Ansible deploy/expose playbooks, referenced
 * config/secret files, Traefik dynamic config) are captured from the in-memory workspace when the mocked
 * process is built, and golden-compared against `tests/Behat/expected/compose/base/*`, mirroring how
 * `KubernetesTrait` golden-compares manifests.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
trait DockerComposeTrait
{
    /**
     * Deterministic name of the SSH private key file the real `RunnerFactory` materializes, making the
     * "--private-key" argument assertable. It must not start with "space-behat-compose-", or
     * `normalizeComposePlaybook()` would rewrite it and the artifact capture would mistake it for a working
     * directory.
     */
    private const COMPOSE_KEY_FILE_NAME = 'space-behat-ansible-key';

    /**
     * Timeout handed to the real `RunnerFactory`, proving it reaches the (mocked) `Process` factory.
     */
    private const COMPOSE_ANSIBLE_TIMEOUT = 300.0;

    /**
     * SSH private key carried by the docker-compose clusters credentials (mirrors `.env.test` and the
     * account-cluster fixture) and materialized by the real `RunnerFactory`.
     */
    private const COMPOSE_SSH_PRIVATE_KEY = 'fake-ssh-private-key';

    /**
     * SSH login user, resolved by the real `RunnerFactory` either from `ClusterCredentials::getUsername()`
     * (account cluster) or from the "ssh://deployer@..." master address (catalog cluster).
     */
    private const COMPOSE_SSH_USER = 'deployer';

    /**
     * Address of the "Demo Compose Cluster" catalog entry, rendered into the Ansible inventory.
     */
    private const COMPOSE_SSH_HOST = 'docker-host.behat.test';

    private const COMPOSE_SSH_PORT = 22;

    /**
     * @var array<string, string>
     */
    private array $composeArtifacts = [];

    /**
     * @var array<string, string>
     */
    private array $traefikArtifacts = [];

    /**
     * @var array<string, string>
     */
    private array $referencedFiles = [];

    /**
     * Every `ansible-playbook` invocation the real `SymfonyProcessRunner` built, in order.
     *
     * @var array<int, array{command: array<int, string>, timeout: float|null}>
     */
    private array $ansibleRuns = [];

    /**
     * Inventory rendered by the Driver for each stage, keyed by stage name ("deploy", "expose").
     *
     * @var array<string, string>
     */
    private array $ansibleInventories = [];

    private ?string $ansibleKeyFileContent = null;

    //Defaults are applied in aDockerComposeOrchestrator() rather than here: Symfony's ReflectionClassResource
    //reflects this trait outside of any class, where "self::" cannot resolve a trait constant.
    private string $expectedComposeSshHost = '';

    private int $expectedComposeSshPort = 0;

    /**
     * Declare the SSH target the current scenario deploys to, when it is not the "Demo Compose Cluster"
     * catalog entry (see the account-cluster docker-compose fixture).
     */
    public function setExpectedComposeSshTarget(string $host, int $port = 22): void
    {
        $this->expectedComposeSshHost = $host;
        $this->expectedComposeSshPort = $port;
    }

    #[Given('a docker-compose orchestrator')]
    public function aDockerComposeOrchestrator(): void
    {
        $this->composeArtifacts = [];
        $this->traefikArtifacts = [];
        $this->referencedFiles = [];
        $this->ansibleRuns = [];
        $this->ansibleInventories = [];
        $this->ansibleKeyFileContent = null;
        $this->expectedComposeSshHost = self::COMPOSE_SSH_HOST;
        $this->expectedComposeSshPort = self::COMPOSE_SSH_PORT;

        //Drive the real DockerCompose Driver against an in-memory Flysystem instead of the disk-backed
        //LocalFilesystemAdapter wired by di.php, so the scenario never alters the filesystem. The mocked
        //Ansible process reads the artifacts the Driver wrote back from this same in-memory filesystem.
        $workspaceFilesystem = new Filesystem(new InMemoryFilesystemAdapter());

        $templatesDir = $this->kernel->getProjectDir()
            . '/vendor/teknoo/east-paas/infrastructures/DockerCompose/templates';
        $templatesFilesystem = new Filesystem(new InMemoryFilesystemAdapter());
        $templatesFilesystem->write(
            'deploy.yml.template',
            (string) file_get_contents($templatesDir . '/deploy.yml.template'),
        );
        $templatesFilesystem->write(
            'expose.yml.template',
            (string) file_get_contents($templatesDir . '/expose.yml.template'),
        );

        $capture = function (string $playbookPath) use ($workspaceFilesystem): void {
            //The Driver builds the runner-facing playbook path as "{workspaceRoot}/{workingDir}/{stage}.yml"
            //but writes through the filesystem with the relative "{workingDir}/...". workingDir is a single
            //path segment, so basename(dirname()) recovers it whatever the (here empty) workspaceRoot is.
            $playbookName = basename($playbookPath);
            $workingDir = basename(dirname($playbookPath));

            $playbookRelative = $workingDir . '/' . $playbookName;
            if ($workspaceFilesystem->fileExists($playbookRelative)) {
                $this->composeArtifacts[$playbookName] = $workspaceFilesystem->read($playbookRelative);
            }

            //The full Compose Specification file is produced during the deploy stage (services, networks,
            //volumes, ...); the expose stage rewrites a near-empty compose.yaml, so only the deploy one is kept.
            $composeRelative = $workingDir . '/compose.yaml';
            if ('deploy.yml' === $playbookName && $workspaceFilesystem->fileExists($composeRelative)) {
                $this->composeArtifacts['compose.yaml'] = $workspaceFilesystem->read($composeRelative);

                //The config/secret files referenced by the Compose Specification are written next to it under
                //"configs/" and "secrets/"; capture their content keyed by the same forward-slash relative path
                //so they can be golden-compared like the Kubernetes ConfigMap/Secret manifests.
                foreach (['configs', 'secrets'] as $subDir) {
                    $base = $workingDir . '/' . $subDir;
                    foreach ($workspaceFilesystem->listContents($base, true) as $item) {
                        if (!$item->isFile()) {
                            continue;
                        }

                        $relative = $subDir . '/' . substr($item->path(), strlen($base) + 1);
                        $this->referencedFiles[$relative] = $workspaceFilesystem->read($item->path());
                    }
                }
            }

            //The Traefik dynamic configuration is serialized to "<project>.yml" alongside the playbook on the
            //expose stage; capture every "*.yml" sibling but the playbooks themselves (deploy.yml/expose.yml).
            foreach ($workspaceFilesystem->listContents($workingDir, false) as $item) {
                if (!$item->isFile()) {
                    continue;
                }

                $name = basename($item->path());
                if (!str_ends_with($name, '.yml') || 'deploy.yml' === $name || 'expose.yml' === $name) {
                    continue;
                }

                $this->traefikArtifacts[$name] = $workspaceFilesystem->read($item->path());
            }
        };

        //The only mocked seam of the runner layer: the Symfony Process. Everything above it (the real
        //RunnerFactory resolving the SSH user and materializing the private key, and the real
        //SymfonyProcessRunner building the `ansible-playbook` command line) is exercised for real, so the
        //Driver's outputs are proven to reach the process layer.
        $processFactory = function (array $command, ?float $timeout) use ($workspaceFilesystem, $capture): Process {
            $this->ansibleRuns[] = ['command' => $command, 'timeout' => $timeout];

            //$command[1] is the playbook absolute path, $command[3] the inventory one; both live in the per-run
            //working directory, recovered with the same basename(dirname()) trick as $capture.
            $stage = basename($command[1], '.yml');
            $inventoryRelative = basename(dirname($command[3])) . '/inventory.ini';
            if ($workspaceFilesystem->fileExists($inventoryRelative)) {
                $this->ansibleInventories[$stage] = $workspaceFilesystem->read($inventoryRelative);
            }

            //The materialized SSH private key must be read here: RunnerFactory::__destruct() deletes it as soon
            //as the factory goes out of scope.
            if ($workspaceFilesystem->fileExists(self::COMPOSE_KEY_FILE_NAME)) {
                $this->ansibleKeyFileContent = $workspaceFilesystem->read(self::COMPOSE_KEY_FILE_NAME);
            }

            ($capture)($command[1]);

            //A fresh double per call: the Driver runs two stages (deploy then expose) through two distinct
            //runners.
            $process = new Generator()->testDouble(
                type: Process::class,
                mockObject: true,
                callOriginalConstructor: false,
                callOriginalClone: false,
            );

            $process->expects(new AnyInvokedCountMatcher())->method('run');
            $process->method('isSuccessful')->willReturn(true);
            $process->method('getOutput')->willReturn('PLAY RECAP behat : ok=6 changed=4 failed=0');
            $process->method('getErrorOutput')->willReturn('');

            return $process;
        };

        //The real factory, pointed at the same in-memory workspace so the private key never touches the disk,
        //with a deterministic key file name making the "--private-key" argument assertable.
        $runnerFactory = new RunnerFactory(
            filesystem: $workspaceFilesystem,
            tmpDir: '',
            playbookBinary: 'ansible-playbook',
            timeout: self::COMPOSE_ANSIBLE_TIMEOUT,
            keyFileNameFactory: static fn (): string => self::COMPOSE_KEY_FILE_NAME,
            //Only overridden to inject the mocked $processFactory: the runner itself is the real one.
            runnerBuilder: static fn (
                string $playbookBinary,
                ?float $timeout,
                ?string $sshUser,
                ?string $privateKeyFile,
            ): RunnerInterface => new SymfonyProcessRunner(
                playbookBinary: $playbookBinary,
                timeout: $timeout,
                sshUser: $sshUser,
                privateKeyFile: $privateKeyFile,
                processFactory: $processFactory,
            ),
        );

        //Build the Driver directly with the in-memory filesystems and register it on the Directory, overriding
        //the disk-backed driver di.php would otherwise provide.
        $driver = new DockerComposeDriver(
            runnerFactory: $runnerFactory,
            transcribers: $this->sfContainer->get(TranscriberCollectionInterface::class),
            workspaceFilesystem: $workspaceFilesystem,
            templatesFilesystem: $templatesFilesystem,
            workspaceRoot: '',
            tmpDirFactory: static fn (): string => 'space-behat-compose-' . uniqid('', true),
            templates: [
                'deploy' => 'deploy.yml.template',
                'expose' => 'expose.yml.template',
            ],
        );

        $this->sfContainer->get(Directory::class)->register('docker-compose', $driver);

        //The docker-compose orchestrator also selects the cluster type for the project under test so the
        //deployment targets a docker-compose cluster (replaces the former dedicated "the project is deployed
        //on a docker-compose cluster" step).
        $this->setClusterOverride(
            type: 'docker-compose',
            name: 'Demo Compose Cluster',
            address: 'ssh://deployer@docker-host.behat.test:22',
        );
    }

    #[Then('no docker compose configuration must be created')]
    public function noDockerComposeConfigurationMustBeCreated(): void
    {
        Assert::assertEmpty(
            $this->composeArtifacts,
            'No Docker Compose artifact was expected (the job errored or was denied before deployment)',
        );
        Assert::assertEmpty(
            $this->traefikArtifacts,
            'No Traefik artifact was expected (the job errored or was denied before deployment)',
        );
        Assert::assertEmpty(
            $this->ansibleRuns,
            'No Ansible playbook run was expected (the job errored or was denied before deployment)',
        );
    }

    #[Then('some docker compose configuration has been created')]
    public function someDockerComposeConfigurationHasBeenCreated(): void
    {
        //The mocked Ansible process captured the artifacts written by the driver into its working directory
        //(mirroring the $this->manifests capture for Kubernetes) instead of running Ansible/Docker.
        Assert::assertNotEmpty(
            $this->composeArtifacts,
            'No Docker Compose artifact has been captured by the mocked Ansible process',
        );
        Assert::assertArrayHasKey(
            'compose.yaml',
            $this->composeArtifacts,
            'The compose.yaml file has not been generated',
        );
        Assert::assertArrayHasKey(
            'deploy.yml',
            $this->composeArtifacts,
            'The deploy Ansible playbook has not been generated',
        );

        if (!empty($_ENV['SPACE_DC_DUMP_GOLDEN'])) {
            $this->dumpComposeGolden();

            return;
        }

        $expected = $this->loadExpectedComposeArtifacts();

        //Full golden comparison (like the Kubernetes manifests): the generated Compose Specification must
        //match the reviewed expected file byte for byte.
        Assert::assertSame(
            $expected['compose.yaml'],
            $this->composeArtifacts['compose.yaml'],
            'The generated compose.yaml does not match the expected golden file',
        );

        //The deploy playbook is compared after normalizing the per-run working directory (uniqid) baked into
        //its "src" paths; everything else (vars block, paas_files/paas_reset_volumes/paas_jobs, tasks) is golden.
        Assert::assertSame(
            $expected['deploy.yml'],
            $this->normalizeComposePlaybook($this->composeArtifacts['deploy.yml']),
            'The generated deploy.yml playbook does not match the expected golden file',
        );

        //Every config/secret file referenced by the Compose Specification must match its expected content.
        $actualReferencedFiles = $this->referencedFiles;
        ksort($actualReferencedFiles);
        Assert::assertSame(
            $expected['referencedFiles'],
            $actualReferencedFiles,
            'The referenced config/secret files do not match the expected golden files',
        );

        $this->assertAnsibleRun('deploy');
    }

    #[Then('some traefik configuration has been created')]
    public function someTraefikConfigurationHasBeenCreated(): void
    {
        Assert::assertNotEmpty(
            $this->traefikArtifacts,
            'No Traefik dynamic configuration artifact has been captured by the mocked Ansible process',
        );

        //The expose playbook must have been generated to drop the Traefik dynamic file in the watched directory.
        Assert::assertArrayHasKey(
            'expose.yml',
            $this->composeArtifacts,
            'The expose Ansible playbook has not been generated',
        );

        if (!empty($_ENV['SPACE_DC_DUMP_GOLDEN'])) {
            $this->dumpComposeGolden();

            return;
        }

        $expected = $this->loadExpectedComposeArtifacts();

        //The expose playbook is compared after normalizing the per-run working directory (uniqid) baked into
        //its "src" path; the vars block (paas_certs) and tasks are golden.
        Assert::assertSame(
            $expected['expose.yml'],
            $this->normalizeComposePlaybook($this->composeArtifacts['expose.yml']),
            'The generated expose.yml playbook does not match the expected golden file',
        );

        //Exactly one Traefik dynamic configuration file ("<project>.yml") is produced, and it must match the
        //expected golden file (routers, services, TLS) byte for byte.
        Assert::assertCount(
            1,
            $this->traefikArtifacts,
            'Exactly one Traefik dynamic configuration file is expected',
        );
        Assert::assertSame(
            $expected['traefik'],
            (string) reset($this->traefikArtifacts),
            'The generated Traefik dynamic configuration does not match the expected golden file',
        );

        $this->assertAnsibleRun('expose');
    }

    /**
     * Assert the real runner layer turned the Driver's outputs into the expected `ansible-playbook`
     * invocation for the given stage: the exact argv (playbook, inventory, extra vars, SSH user, private
     * key), the timeout, the rendered single-host inventory and the materialized SSH private key.
     *
     * @throws JsonException
     */
    private function assertAnsibleRun(string $stage): void
    {
        $runs = array_values(
            array_filter(
                $this->ansibleRuns,
                static fn (array $run): bool => basename($run['command'][1]) === $stage . '.yml',
            )
        );

        Assert::assertCount(
            1,
            $runs,
            'Exactly one Ansible run was expected for the "' . $stage . '" stage',
        );

        $run = $runs[0];

        Assert::assertSame(
            self::COMPOSE_ANSIBLE_TIMEOUT,
            $run['timeout'],
            'The configured Ansible timeout has not been forwarded to the process',
        );

        //The project name is not hardcoded here: it is read back from the playbook already golden-compared by
        //the caller, so a single golden set keeps covering every project name/prefix variant.
        Assert::assertSame(
            1,
            preg_match('#^\s*paas_project:\s*"([^"]+)"#m', $this->composeArtifacts[$stage . '.yml'], $matches),
            'The generated ' . $stage . '.yml playbook does not declare a paas_project var',
        );

        Assert::assertSame(
            [
                'ansible-playbook',
                '__WORKDIR__/' . $stage . '.yml',
                '--inventory',
                '__WORKDIR__/inventory.ini',
                '--extra-vars',
                json_encode(['paas_project' => $matches[1]], JSON_THROW_ON_ERROR),
                '--user',
                self::COMPOSE_SSH_USER,
                '--private-key',
                '/' . self::COMPOSE_KEY_FILE_NAME,
            ],
            array_map($this->normalizeComposePlaybook(...), $run['command']),
            'The `ansible-playbook` command line built for the "' . $stage . '" stage is not the expected one',
        );

        Assert::assertSame(
            "[docker_host]\n{$this->expectedComposeSshHost} ansible_host={$this->expectedComposeSshHost}"
            . " ansible_port={$this->expectedComposeSshPort}\n",
            $this->ansibleInventories[$stage] ?? null,
            'The Ansible inventory rendered for the "' . $stage . '" stage is not the expected one',
        );

        Assert::assertSame(
            self::COMPOSE_SSH_PRIVATE_KEY,
            $this->ansibleKeyFileContent,
            'The SSH private key has not been materialized from the cluster credentials',
        );
    }

    /**
     * Replace the per-run, host-dependent working directory ("space-behat-compose-<uniqid>") baked into the
     * rendered playbooks by a stable "__WORKDIR__" placeholder so deploy.yml/expose.yml (and the JSON
     * paas_files/paas_certs "src" lists they embed) can be golden-compared.
     */
    private function normalizeComposePlaybook(string $text): string
    {
        return (string) preg_replace(
            '#[^"\s]*/space-behat-compose-[0-9a-f]+\.[0-9a-f]+#',
            '__WORKDIR__',
            $text,
        );
    }

    /**
     * The generated Compose artifacts depend only on the deployed project (name/prefix), its paas file and the
     * quota mode; encryption, Kubernetes version, ingress-provider annotations and HNC do not change them. This
     * key collapses those irrelevant axes so scenarios sharing the same compose output share one golden set.
     */
    private function composeVariantKey(): string
    {
        $paasFile = (string) $this->paasFile;
        $key = strtolower(basename(dirname($paasFile)) . '-' . pathinfo($paasFile, PATHINFO_FILENAME));

        if (!empty($this->projectPrefix)) {
            $key .= '-prefix-' . strtolower((string) preg_replace('/[^A-Za-z0-9]+/', '-', $this->projectPrefix));
        }

        if (!empty($this->quotasMode)) {
            $key .= '-quota-' . $this->quotasMode;
        }

        return $key;
    }

    private function composeVariantDir(): string
    {
        return __DIR__ . '/../expected/compose/' . $this->composeVariantKey();
    }

    /**
     * Load the reviewed expected Docker Compose artifacts for the current scenario's variant.
     *
     * @return array{
     *     compose.yaml: string,
     *     deploy.yml: string,
     *     expose.yml: string,
     *     traefik: string,
     *     referencedFiles: array<string, string>
     * }
     */
    private function loadExpectedComposeArtifacts(): array
    {
        $dir = $this->composeVariantDir();

        $referencedFiles = [];
        foreach (['configs', 'secrets'] as $subDir) {
            $base = $dir . '/refs/' . $subDir;
            if (!is_dir($base)) {
                continue;
            }

            /** @var SplFileInfo $file */
            foreach (
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS),
                ) as $file
            ) {
                if (!$file->isFile()) {
                    continue;
                }

                $relative = $subDir . '/' . substr($file->getPathname(), strlen($base) + 1);
                $referencedFiles[$relative] = (string) file_get_contents($file->getPathname());
            }
        }

        ksort($referencedFiles);

        return [
            'compose.yaml' => (string) file_get_contents($dir . '/compose.yaml'),
            'deploy.yml' => (string) file_get_contents($dir . '/deploy.yml'),
            'expose.yml' => (string) file_get_contents($dir . '/expose.yml'),
            'traefik' => (string) file_get_contents($dir . '/traefik.yml'),
            'referencedFiles' => $referencedFiles,
        ];
    }

    /**
     * Golden-regeneration affordance: with SPACE_DC_DUMP_GOLDEN set, write the captured artifacts to the
     * expected/ directory (normalizing the per-run working dir) instead of asserting, so they can be reviewed
     * and committed. Never runs in a normal test run.
     */
    private function dumpComposeGolden(): void
    {
        $dir = $this->composeVariantDir();
        if (!is_dir($dir)) {
            mkdir($dir, 0o775, true);
        }

        if (isset($this->composeArtifacts['compose.yaml'])) {
            file_put_contents($dir . '/compose.yaml', $this->composeArtifacts['compose.yaml']);
        }
        if (isset($this->composeArtifacts['deploy.yml'])) {
            file_put_contents(
                $dir . '/deploy.yml',
                $this->normalizeComposePlaybook($this->composeArtifacts['deploy.yml']),
            );
        }
        if (isset($this->composeArtifacts['expose.yml'])) {
            file_put_contents(
                $dir . '/expose.yml',
                $this->normalizeComposePlaybook($this->composeArtifacts['expose.yml']),
            );
        }
        foreach ($this->traefikArtifacts as $content) {
            file_put_contents($dir . '/traefik.yml', $content);
        }
        foreach ($this->referencedFiles as $relative => $content) {
            $target = $dir . '/refs/' . $relative;
            if (!is_dir(dirname($target))) {
                mkdir(dirname($target), 0o775, true);
            }
            file_put_contents($target, $content);
        }
    }
}
