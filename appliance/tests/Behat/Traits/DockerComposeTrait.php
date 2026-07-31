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
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\Assert;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SensitiveParameter;
use SplFileInfo;
use Teknoo\East\Paas\Cluster\Directory;
use Teknoo\East\Paas\Infrastructures\DockerCompose\Contracts\RunnerFactoryInterface;
use Teknoo\East\Paas\Infrastructures\DockerCompose\Contracts\RunnerInterface;
use Teknoo\East\Paas\Infrastructures\DockerCompose\Contracts\Transcriber\TranscriberCollectionInterface;
use Teknoo\East\Paas\Infrastructures\DockerCompose\Driver as DockerComposeDriver;
use Teknoo\East\Paas\Object\ClusterCredentials;
use Teknoo\Recipe\Promise\PromiseInterface;

use function basename;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function getenv;
use function is_dir;
use function ksort;
use function mkdir;
use function pathinfo;
use function preg_replace;
use function reset;
use function str_ends_with;
use function strlen;
use function strtolower;
use function substr;
use function uniqid;

use const PATHINFO_FILENAME;

/**
 * Behat steps exercising a docker-compose deployment end-to-end, modelled on the East PaaS docker-compose
 * Behat test (`vendor/teknoo/east-paas/tests/behat/FeatureContext.php::aDockerComposeOrchestrator`).
 *
 * The real `DockerCompose\Driver` runs against two in-memory Flysystem filesystems (never touching the disk),
 * and a fake `RunnerInterface`/`RunnerFactoryInterface` captures the artifacts the Driver produced (compose
 * specification, Ansible deploy/expose playbooks, referenced config/secret files, Traefik dynamic config)
 * instead of executing Ansible/SSH. Captured artifacts are golden-compared against
 * `tests/Behat/expected/compose/base/*`, mirroring how `KubernetesTrait` golden-compares manifests.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
trait DockerComposeTrait
{
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

    #[Given('a docker-compose orchestrator')]
    public function aDockerComposeOrchestrator(): void
    {
        $this->composeArtifacts = [];
        $this->traefikArtifacts = [];
        $this->referencedFiles = [];

        //Drive the real DockerCompose Driver against an in-memory Flysystem instead of the disk-backed
        //LocalFilesystemAdapter wired by di.php, so the scenario never alters the filesystem. The fake runner
        //reads the artifacts the Driver wrote back from this same in-memory filesystem.
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

        $runner = new class ($capture) implements RunnerInterface {
            /**
             * @param callable(string): void $capture
             */
            public function __construct(
                private $capture,
            ) {
            }

            public function run(
                string $playbookPath,
                string $inventoryPath,
                array $extraVars,
                #[SensitiveParameter] ?ClusterCredentials $credentials,
                PromiseInterface $promise,
            ): RunnerInterface {
                ($this->capture)($playbookPath);

                $promise->success('docker-compose fake runner: playbook captured, nothing executed');

                return $this;
            }
        };

        $runnerFactory = new class ($runner) implements RunnerFactoryInterface {
            public function __construct(
                private readonly RunnerInterface $runner,
            ) {
            }

            public function __invoke(
                string $url,
                #[SensitiveParameter] ?ClusterCredentials $credentials,
            ): RunnerInterface {
                return $this->runner;
            }
        };

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
    }

    #[Then('some docker compose configuration has been created')]
    public function someDockerComposeConfigurationHasBeenCreated(): void
    {
        //The fake RunnerInterface captured the artifacts written by the driver into its working directory
        //(mirroring the $this->manifests capture for Kubernetes) instead of running Ansible/Docker.
        Assert::assertNotEmpty(
            $this->composeArtifacts,
            'No Docker Compose artifact has been captured by the fake runner',
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
    }

    #[Then('some traefik configuration has been created')]
    public function someTraefikConfigurationHasBeenCreated(): void
    {
        Assert::assertNotEmpty(
            $this->traefikArtifacts,
            'No Traefik dynamic configuration artifact has been captured by the fake runner',
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
