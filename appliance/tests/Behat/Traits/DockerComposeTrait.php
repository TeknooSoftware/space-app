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
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Process\Process;
use Teknoo\East\Paas\Cluster\Directory;
use Teknoo\East\Paas\Infrastructures\DockerCompose\Contracts\RunnerFactoryInterface;
use Teknoo\East\Paas\Infrastructures\DockerCompose\Contracts\Transcriber\TranscriberCollectionInterface;
use Teknoo\East\Paas\Infrastructures\DockerCompose\Driver as DockerComposeDriver;
use Teknoo\East\Paas\Recipe\Step\Worker\Deploying;
use Teknoo\Space\Object\Persisted\AccountRegistry;

use function array_filter;
use function array_map;
use function array_values;
use function basename;
use function count;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function getenv;
use function is_dir;
use function json_encode;
use function ksort;
use function mkdir;
use function sys_get_temp_dir;
use function unlink;
use function pathinfo;
use function preg_match;
use function preg_replace;
use function reset;
use function rmdir;
use function scandir;
use function str_contains;
use function str_starts_with;
use function str_ends_with;
use function strlen;
use function strtolower;
use function substr;
use function uniqid;

use const JSON_THROW_ON_ERROR;
use const PATHINFO_FILENAME;
use const PHP_EOL;

/**
 * Behat steps exercising a docker-compose deployment end-to-end, modelled on the East PaaS docker-compose
 * Behat test (`vendor/teknoo/east-paas/tests/behat/FeatureContext.php::aDockerComposeOrchestrator`).
 *
 * The real `DockerCompose\Driver` runs against two in-memory Flysystem filesystems (never touching the disk),
 * and the whole runner layer above the process is the real one too: the real `RunnerFactory` resolves the SSH
 * login user and materializes the SSH private key into that in-memory workspace, and the real
 * `SymfonyProcessRunner` builds the `ansible-playbook` command line and its environment. Only the lowest
 * seam - the Symfony `Process` - is mocked, so nothing is ever executed while the command line, the Ansible
 * environment (no colors, strict host key checking), the rendered inventory, the resolved SSH user, the
 * materialized private key and the materialized `known_hosts` all become assertable (see `assertAnsibleRun()`).
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
    use AnsibleRunnerDoubleTrait;

    /**
     * Deterministic name of the SSH private key file the real `RunnerFactory` materializes, making the
     * "--private-key" argument assertable. It must not start with "space-behat-compose-", or
     * `normalizeComposePlaybook()` would rewrite it and the artifact capture would mistake it for a working
     * directory.
     */
    private const COMPOSE_KEY_FILE_NAME = 'space-behat-ansible-key';

    /**
     * Deterministic name of the `known_hosts` file the real `RunnerFactory` materializes from the cluster
     * credentials' CA certificate field (second file written after the private key), making the
     * `ANSIBLE_SSH_COMMON_ARGS` environment variable assertable.
     */
    private const COMPOSE_KNOWN_HOSTS_FILE_NAME = 'space-behat-ansible-known-hosts';

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
     * SSH host public key carried by the "Demo Compose Cluster" catalog entry (`ssh.known_hosts` in `.env.test`),
     * bound by the real `RunnerFactory` to the cluster host in the materialized `known_hosts` file.
     */
    private const COMPOSE_SSH_KNOWN_HOSTS = 'fake-known-hosts';

    /**
     * Address of the "Demo Compose Cluster" catalog entry, rendered into the Ansible inventory.
     */
    private const COMPOSE_SSH_HOST = 'docker-host.behat.test';

    private const COMPOSE_SSH_PORT = 22;

    /**
     * Deterministic names of the SSH private key and of the known_hosts the real `RunnerFactory` materializes for
     * the account provisioning playbooks (see "a fake Ansible runner for the account provisioning").
     */
    private const PROVISIONING_KEY_FILE_NAME = 'space-behat-provisioning-key';

    private const PROVISIONING_KNOWN_HOSTS_FILE_NAME = 'space-behat-provisioning-known-hosts';

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
     * Every `ansible-playbook` invocation the real `SymfonyProcessRunner` built, in order, with the environment
     * it set on the process.
     *
     * @var array<int, array{command: array<int, string>, timeout: float|null, env: array<string, string>}>
     */
    private array $ansibleRuns = [];

    /**
     * In-memory workspace of the Driver and of the RunnerFactory, kept to assert the per-run working directory
     * is removed once the playbooks ran.
     */
    private ?Filesystem $composeWorkspaceFilesystem = null;

    /**
     * Inventory rendered by the Driver for each stage, keyed by stage name ("deploy", "expose").
     *
     * @var array<string, string>
     */
    private array $ansibleInventories = [];

    private ?string $ansibleKeyFileContent = null;

    private ?string $ansibleKnownHostsContent = null;

    /**
     * Every `ansible-playbook` invocation of the account provisioning steps, in order, with the inventory each
     * one received (indexed like the runs).
     *
     * @var array<int, array{command: array<int, string>, timeout: float|null, env: array<string, string>}>
     */
    private array $provisioningAnsibleRuns = [];

    /**
     * @var array<int, string|null>
     */
    private array $provisioningAnsibleInventories = [];

    private ?Filesystem $provisioningInventoryFilesystem = null;

    private ?string $provisioningKeyFileContent = null;

    private ?string $provisioningKnownHostsContent = null;

    //Defaults are applied in aDockerComposeOrchestrator() rather than here: Symfony's ReflectionClassResource
    //reflects this trait outside of any class, where "self::" cannot resolve a trait constant.
    private string $expectedComposeSshHost = '';

    private int $expectedComposeSshPort = 0;

    private string $expectedComposeKnownHosts = '';

    /**
     * Declare the SSH target the current scenario deploys to, when it is not the "Demo Compose Cluster"
     * catalog entry (see the account-cluster docker-compose fixture), with the SSH host public key its
     * credentials carry.
     */
    public function setExpectedComposeSshTarget(string $host, int $port = 22, string $knownHosts = ''): void
    {
        $this->expectedComposeSshHost = $host;
        $this->expectedComposeSshPort = $port;
        $this->expectedComposeKnownHosts = $knownHosts;
    }

    #[Given('a docker-compose orchestrator')]
    public function aDockerComposeOrchestrator(): void
    {
        $this->ansibleRuns = [];
        $this->ansibleInventories = [];
        $this->ansibleKeyFileContent = null;
        $this->ansibleKnownHostsContent = null;
        $this->expectedComposeSshHost = self::COMPOSE_SSH_HOST;
        $this->expectedComposeSshPort = self::COMPOSE_SSH_PORT;
        $this->expectedComposeKnownHosts = self::COMPOSE_SSH_KNOWN_HOSTS;

        //Drive the real DockerCompose Driver against an in-memory Flysystem instead of the disk-backed
        //LocalFilesystemAdapter wired by di.php, so the scenario never alters the filesystem. The mocked
        //Ansible process reads the artifacts the Driver wrote back from this same in-memory filesystem.
        $workspaceFilesystem = new Filesystem(new InMemoryFilesystemAdapter());
        $this->composeWorkspaceFilesystem = $workspaceFilesystem;

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
            //$command[1] is the playbook absolute path, $command[3] the inventory one; both live in the per-run
            //working directory, recovered with the same basename(dirname()) trick as $capture.
            $stage = basename($command[1], '.yml');
            $inventoryRelative = basename(dirname($command[3])) . '/inventory.ini';
            if ($workspaceFilesystem->fileExists($inventoryRelative)) {
                $this->ansibleInventories[$stage] = $workspaceFilesystem->read($inventoryRelative);
            }

            //The materialized SSH private key and known_hosts must be read here: RunnerFactory::__destruct()
            //deletes them as soon as the factory goes out of scope.
            if ($workspaceFilesystem->fileExists(self::COMPOSE_KEY_FILE_NAME)) {
                $this->ansibleKeyFileContent = $workspaceFilesystem->read(self::COMPOSE_KEY_FILE_NAME);
            }
            if ($workspaceFilesystem->fileExists(self::COMPOSE_KNOWN_HOSTS_FILE_NAME)) {
                $this->ansibleKnownHostsContent = $workspaceFilesystem->read(self::COMPOSE_KNOWN_HOSTS_FILE_NAME);
            }

            ($capture)($command[1]);

            //A fresh double per call: the Driver runs two stages (deploy then expose) through two distinct
            //runners.
            return $this->buildAnsibleProcessDouble(
                runs: $this->ansibleRuns,
                command: $command,
                timeout: $timeout,
                successful: true,
                output: 'PLAY RECAP behat : ok=6 changed=4 failed=0',
            );
        };

        //The real factory, pointed at the same in-memory workspace, materializing the private key then the
        //known_hosts for each stage (deploy, expose).
        $runnerFactory = $this->buildAnsibleRunnerFactory(
            filesystem: $workspaceFilesystem,
            timeout: self::COMPOSE_ANSIBLE_TIMEOUT,
            materializedFileNames: [self::COMPOSE_KEY_FILE_NAME, self::COMPOSE_KNOWN_HOSTS_FILE_NAME],
            processFactory: $processFactory,
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

        //The generated Compose Specification must also be accepted by the real Compose (schema validation:
        //resource keys and units, secrets/configs mounts...), when a Docker CLI with the compose plugin is
        //available on the machine running the suite.
        $this->validateComposeFileWithDocker();

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
        $this->assertComposeWorkingDirectoriesRemoved();
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
        $this->assertComposeWorkingDirectoriesRemoved();
    }

    #[Then('the docker compose deployment history must warn about the host ports of :service')]
    public function theDockerComposeDeploymentHistoryMustWarnAboutTheHostPortsOf(string $service): void
    {
        $job = $this->firstJob();

        //The Driver reports the warnings collected by the Accumulator (here: a public service on a replicated
        //pod cannot publish host ports) in the deploy result, dispatched to the job history by the Deploying
        //step. Walk the history chain back to that entry.
        $warnings = null;
        foreach ($this->historyEntries($job->getHistory()) as $entry) {
            if (Deploying::class . ':Result' === $entry->getMessage()) {
                $warnings = $entry->getExtra()['warnings'] ?? [];

                break;
            }
        }

        Assert::assertIsArray($warnings, 'The deployment result is missing from the job history');
        Assert::assertNotEmpty($warnings, 'The deployment result carries no warning');

        $found = false;
        foreach ($warnings as $warning) {
            if (str_contains((string) $warning, "`{$service}`") && str_contains((string) $warning, 'host ports')) {
                $found = true;
            }
        }

        Assert::assertTrue(
            $found,
            "No warning about the host ports of the service `{$service}` in "
                . json_encode($warnings, JSON_THROW_ON_ERROR),
        );
    }

    /**
     * The per-run working directory holds the secret values, the TLS private keys and the SSH inventory: the
     * Driver must remove it from the worker once the playbook ran, whatever the outcome. The private key and
     * known_hosts files are removed by the RunnerFactory destructor.
     */
    private function assertComposeWorkingDirectoriesRemoved(): void
    {
        Assert::assertNotNull($this->composeWorkspaceFilesystem);

        $leftovers = [];
        foreach ($this->composeWorkspaceFilesystem->listContents('', false) as $item) {
            $name = basename($item->path());
            if (str_starts_with($name, 'space-behat-compose-')) {
                $leftovers[] = $name;
            }
        }

        Assert::assertSame(
            [],
            $leftovers,
            'The per-run working directories must be removed from the worker once the playbooks ran',
        );
    }

    /**
     * Run `docker compose config` on the generated Compose Specification (with its referenced files) to prove
     * the real Compose accepts it. Skipped when no docker CLI with the compose plugin is available.
     */
    private function validateComposeFileWithDocker(): void
    {
        static $dockerAvailable = null;
        if (null === $dockerAvailable) {
            $probe = new Process(['docker', 'compose', 'version']);
            $probe->run();
            $dockerAvailable = $probe->isSuccessful();
        }

        if (!$dockerAvailable) {
            return;
        }

        $dir = sys_get_temp_dir() . '/space-behat-compose-check-' . uniqid('', true);
        mkdir($dir, 0o700, true);

        try {
            file_put_contents($dir . '/compose.yaml', $this->composeArtifacts['compose.yaml']);
            foreach ($this->referencedFiles as $relative => $content) {
                $path = $dir . '/' . $relative;
                if (!is_dir(dirname($path))) {
                    mkdir(dirname($path), 0o700, true);
                }

                file_put_contents($path, $content);
            }

            $process = new Process(
                ['docker', 'compose', '--project-directory', $dir, '-f', $dir . '/compose.yaml', 'config', '-q'],
                $dir,
            );
            $process->run();

            Assert::assertTrue(
                $process->isSuccessful(),
                'The generated compose.yaml is rejected by `docker compose config`: '
                    . $process->getErrorOutput() . $process->getOutput(),
            );
        } finally {
            $this->removeComposeDirectory($dir);
        }
    }

    private function removeComposeDirectory(string $dir): void
    {
        foreach ((array) scandir($dir) as $entry) {
            if ('.' === $entry || '..' === $entry) {
                continue;
            }

            $path = $dir . '/' . $entry;
            if (is_dir($path)) {
                $this->removeComposeDirectory($path);

                continue;
            }

            unlink($path);
        }

        rmdir($dir);
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

        $this->assertAnsibleSshMaterial(
            keyFileContent: $this->ansibleKeyFileContent,
            knownHostsContent: $this->ansibleKnownHostsContent,
            host: $this->expectedComposeSshHost,
            knownHostsFileName: self::COMPOSE_KNOWN_HOSTS_FILE_NAME,
            env: $run['env'],
            process: '"' . $stage . '"',
        );
    }

    /**
     * The runner materialized the cluster credentials: the private key, and the SSH host public key carried by the
     * credentials' CA certificate field, which the factory binds to the cluster host in a known_hosts file and
     * against which the runner enforces a strict host key checking.
     *
     * @param array<string, string> $env
     */
    private function assertAnsibleSshMaterial(
        ?string $keyFileContent,
        ?string $knownHostsContent,
        string $host,
        string $knownHostsFileName,
        array $env,
        string $process,
    ): void {
        Assert::assertSame(
            self::COMPOSE_SSH_PRIVATE_KEY . PHP_EOL,
            $keyFileContent,
            'The SSH private key has not been materialized from the cluster credentials',
        );

        Assert::assertSame(
            "{$host} {$this->expectedComposeKnownHosts}" . PHP_EOL,
            $knownHostsContent,
            'The SSH known_hosts has not been materialized from the cluster credentials',
        );

        Assert::assertSame(
            [
                'ANSIBLE_NOCOLOR' => '1',
                'ANSIBLE_FORCE_COLOR' => '0',
                'ANSIBLE_HOST_KEY_CHECKING' => 'True',
                'ANSIBLE_SSH_COMMON_ARGS' => '-o UserKnownHostsFile=/' . $knownHostsFileName
                    . ' -o StrictHostKeyChecking=yes',
            ],
            $env,
            'The Ansible environment set on the ' . $process . ' process is not the expected one',
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
        //The referenced files tree is rebuilt from scratch: a stale file (a removed key, a renamed secret)
        //would otherwise stay in the golden set and fail the comparison.
        if (is_dir($dir . '/refs')) {
            $this->removeComposeDirectory($dir . '/refs');
        }
        foreach ($this->referencedFiles as $relative => $content) {
            $target = $dir . '/refs/' . $relative;
            if (!is_dir(dirname($target))) {
                mkdir(dirname($target), 0o775, true);
            }
            file_put_contents($target, $content);
        }
    }

    /**
     * Swap the East PaaS runner factory and the Ansible inventory Flysystem of the `PlaybookRunner` the account
     * provisioning steps (`LogDeployUserInRegistries`) run their playbooks through: it holds the DI runner
     * factory and not the Driver the
     * "a docker-compose orchestrator" step replaces: without this double a scenario installing an environment on a
     * docker-compose cluster would try to reach a real host over SSH. As for the deployment, the whole runner
     * layer above the process is the real one, only the Symfony `Process` is mocked.
     *
     * `Container::set()` refuses to replace an already initialized service, so this step must come before
     * anything resolves the provisioning plans (and before "a docker-compose orchestrator").
     */
    #[Given('a fake Ansible runner for the account provisioning')]
    public function aFakeAnsibleRunnerForTheAccountProvisioning(): void
    {
        $this->provisioningAnsibleRuns = [];
        $this->provisioningAnsibleInventories = [];
        $this->provisioningKeyFileContent = null;
        $this->provisioningKnownHostsContent = null;

        //The inventory is written through an in-memory Flysystem instead of the disk-backed adapter rooted on
        //the worker tmp dir, while its path handed to Ansible still points under that tmp dir.
        $inventoryFilesystem = new Filesystem(new InMemoryFilesystemAdapter());
        $this->provisioningInventoryFilesystem = $inventoryFilesystem;
        $this->sfContainer->set('teknoo.space.flysystem.ansible_inventory', $inventoryFilesystem);

        //Workspace of the real RunnerFactory, where it materializes the private key then the known_hosts.
        $keyFilesystem = new Filesystem(new InMemoryFilesystemAdapter());

        $this->sfContainer->set(
            RunnerFactoryInterface::class,
            $this->buildAnsibleRunnerFactory(
                filesystem: $keyFilesystem,
                timeout: self::COMPOSE_ANSIBLE_TIMEOUT,
                materializedFileNames: [self::PROVISIONING_KEY_FILE_NAME, self::PROVISIONING_KNOWN_HOSTS_FILE_NAME],
                processFactory: fn (array $command, ?float $timeout): Process => $this->buildProvisioningProcess(
                    $command,
                    $timeout,
                    $inventoryFilesystem,
                    $keyFilesystem,
                ),
            ),
        );
    }

    /**
     * Record the `ansible-playbook` invocation and answer it with a mocked successful `Process`. The inventory,
     * the private key and the known_hosts are read here: the `PlaybookRunner` removes the inventory once the
     * playbook has run, and `RunnerFactory::__destruct()` deletes the key files.
     *
     * @param array<int, string> $command
     */
    private function buildProvisioningProcess(
        array $command,
        ?float $timeout,
        Filesystem $inventoryFilesystem,
        Filesystem $keyFilesystem,
    ): Process {
        $runIndex = count($this->provisioningAnsibleRuns);

        //$command[3] is the inventory absolute path; the in-memory adapter is rooted, the file name addresses it
        $inventoryName = basename($command[3]);
        $this->provisioningAnsibleInventories[$runIndex] = null;
        if ($inventoryFilesystem->fileExists($inventoryName)) {
            $this->provisioningAnsibleInventories[$runIndex] = $inventoryFilesystem->read($inventoryName);
        }

        if ($keyFilesystem->fileExists(self::PROVISIONING_KEY_FILE_NAME)) {
            $this->provisioningKeyFileContent = $keyFilesystem->read(self::PROVISIONING_KEY_FILE_NAME);
        }
        if ($keyFilesystem->fileExists(self::PROVISIONING_KNOWN_HOSTS_FILE_NAME)) {
            $this->provisioningKnownHostsContent = $keyFilesystem->read(self::PROVISIONING_KNOWN_HOSTS_FILE_NAME);
        }

        return $this->buildAnsibleProcessDouble(
            runs: $this->provisioningAnsibleRuns,
            command: $command,
            timeout: $timeout,
            successful: true,
            output: 'PLAY RECAP docker_host : ok=1 changed=1 failed=0',
        );
    }

    /**
     * The Docker Compose counterpart of the Kubernetes pull secret: the deploy user of the environment's Docker
     * host is logged in on the account registry, through `registries-login.yml` run over SSH with the cluster
     * credentials. `.env.test` configures no username for the global Space registry, so it is skipped.
     *
     * @throws JsonException
     */
    #[Then('the deploy user of the docker host :host has been logged in on the account registry')]
    public function theDeployUserOfTheDockerHostHasBeenLoggedInOnTheAccountRegistry(string $host): void
    {
        Assert::assertCount(
            1,
            $this->provisioningAnsibleRuns,
            'Exactly one Ansible run was expected to log the deploy user in on the registries',
        );

        $run = $this->provisioningAnsibleRuns[0];

        $accountRegistry = $this->recall(AccountRegistry::class);
        Assert::assertInstanceOf(AccountRegistry::class, $accountRegistry);

        $playbookPath = $this->kernel->getProjectDir()
            . '/infrastructures/AnsibleDockerCompose/templates/registries-login.yml';
        Assert::assertFileExists($playbookPath, 'The registries login playbook is missing');

        $tmpDir = $this->sfContainer->getParameter('teknoo.east.paas.worker.tmp_dir');
        Assert::assertIsString($tmpDir);
        Assert::assertSame(
            $tmpDir,
            dirname($run['command'][3]),
            'The Ansible inventory is not staged under the worker tmp dir',
        );
        Assert::assertSame(
            1,
            preg_match('#^registries-login-inventory-.+\.ini$#', basename($run['command'][3])),
            'The Ansible inventory file name is not the expected one',
        );
        Assert::assertFileDoesNotExist($run['command'][3], 'The Ansible inventory has been written to the disk');
        Assert::assertInstanceOf(Filesystem::class, $this->provisioningInventoryFilesystem);
        Assert::assertFalse(
            $this->provisioningInventoryFilesystem->fileExists(basename($run['command'][3])),
            'The Ansible inventory has not been removed once the registries login playbook has run',
        );

        Assert::assertSame(
            [
                'ansible-playbook',
                $playbookPath,
                '--inventory',
                $run['command'][3],
                '--extra-vars',
                json_encode(
                    [
                        'registries' => [
                            [
                                'host' => $accountRegistry->getRegistryUrl(),
                                'username' => $accountRegistry->getRegistryAccountName(),
                                'password' => $accountRegistry->getRegistryPassword(),
                            ],
                        ],
                    ],
                    JSON_THROW_ON_ERROR,
                ),
                '--user',
                self::COMPOSE_SSH_USER,
                '--private-key',
                '/' . self::PROVISIONING_KEY_FILE_NAME,
            ],
            $run['command'],
            'The `ansible-playbook` command line logging the deploy user in is not the expected one',
        );

        Assert::assertSame(
            "[docker_host]\n{$host} ansible_host={$host} ansible_port=22\n",
            $this->provisioningAnsibleInventories[0] ?? null,
            'The Ansible inventory of the environment host is not the expected one',
        );

        $this->assertAnsibleSshMaterial(
            keyFileContent: $this->provisioningKeyFileContent,
            knownHostsContent: $this->provisioningKnownHostsContent,
            host: $host,
            knownHostsFileName: self::PROVISIONING_KNOWN_HOSTS_FILE_NAME,
            env: $run['env'],
            process: 'registries login',
        );
    }
}
