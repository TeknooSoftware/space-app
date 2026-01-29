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
use Behat\Step\When;
use PHPUnit\Framework\Assert;
use RuntimeException;
use Teknoo\East\Paas\Compilation\CompiledDeployment\Expose\Transport;
use Teknoo\East\Paas\Contracts\Compilation\ConductorInterface;
use Teknoo\East\Paas\Contracts\Hook\HooksCollectionInterface;
use Teknoo\East\Paas\Contracts\Job\JobUnitInterface;
use Teknoo\East\Paas\Contracts\Object\SourceRepositoryInterface;
use Teknoo\East\Paas\Contracts\Repository\CloningAgentInterface;
use Teknoo\East\Paas\Contracts\Workspace\FileInterface;
use Teknoo\East\Paas\Contracts\Workspace\JobWorkspaceInterface;
use Teknoo\East\Paas\Object\Job as JobOrigin;
use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Tests\Behat\HookMock;
use Traversable;

use function current;
use function file_exists;
use function iterator_to_array;
use function json_encode;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
trait BuilderTrait
{
    #[Then('it has an error about a timeout')]
    public function itHasAnErrorAboutATimeout(): void
    {
        $jobs = $this->listObjects(JobOrigin::class);
        Assert::assertNotEmpty($jobs);

        /** @var JobOrigin $job */
        $job = current($jobs);
        Assert::assertInstanceOf(JobOrigin::class, $job);

        Assert::assertTrue($job->getHistory()->isFinal());
        Assert::assertEquals(
            ['Error, time limit exceeded'],
            $job->getHistory()->getExtra()['result'] ?? []
        );
    }

    #[Then('it has an error about a quota exceeded')]
    public function itHasAnErrorAboutQuotaExceeded(): void
    {
        $jobs = $this->listObjects(JobOrigin::class);
        Assert::assertNotEmpty($jobs);

        /** @var JobOrigin $job */
        $job = current($jobs);
        Assert::assertInstanceOf(JobOrigin::class, $job);

        Assert::assertTrue($job->getHistory()->isFinal());
        Assert::assertStringContainsString(
            'Error, remaining available capacity for',
            (string) ($job->getHistory()->getExtra()['result'][1] ?? [])
        );
    }

    #[Given('extensions libraries provided by administrators')]
    public function extensionsLibrariesProvidedByAdministrators(): void
    {
        $lib = $this->sfContainer->get('teknoo.east.paas.compilation.ingresses_extends.library');
        $lib['demo-extends'] = [
            'service' => [
                'name' => 'demo',
                'port' => 8080,
            ],
        ];

        $lib = $this->sfContainer->get('teknoo.east.paas.compilation.pods_extends.library');
        $lib['php-pods-extends'] = [
            'replicas' => 2,
            'requires' => [
                'x86_64',
                'avx',
            ],
            'upgrade' => [
                'max-upgrading-pods' => 2,
                'max-unavailable-pods' => 1,
            ],
            'containers' => [
                'php-run' => [
                    'image' => 'registry.teknoo.software/php-run',
                    'version' => '7.4',
                    'listen' => [8080],
                    'volumes' => [
                        'extra' => [
                            'from' => 'extra',
                            'mount-path' => '/opt/extra',
                        ],
                        'data' => [
                            'mount-path' => '/opt/data',
                            'persistent' => true,
                            'write-many' => false, #default it is at true because replicat is great than 1, force to 1
                            'storage-size' => '3Gi',
                        ],
                        'data-replicated' => [
                            'name' => 'data-replicated',
                            //'write-many' => true, #default it is at true because replicat is great than 1, force to 1
                            'mount-path' => '/opt/data-replicated',
                            'persistent' => true,
                            'storage-provider' => 'replicated-provider',
                            'storage-size' => '3Gi',
                        ],
                        'map' => [
                            'mount-path' => '/map',
                            'from-map' => 'map2',
                        ],
                    ],
                    'variables' => [
                        'SERVER_SCRIPT' => '${SERVER_SCRIPT}',
                    ],
                    'healthcheck' => [
                        'initial-delay-seconds' => 10,
                        'period-seconds' => 30,
                        'probe' => [
                            'command' => ['ps', 'aux', 'php'],
                        ],
                    ],
                ],
            ],
        ];

        $lib = $this->sfContainer->get('teknoo.east.paas.compilation.containers_extends.library');
        $lib['bash-extends'] = [
            'image' => 'registry.hub.docker.com/bash',
            'version' => 'alpine',
        ];

        $lib = $this->sfContainer->get('teknoo.east.paas.compilation.services_extends.library');
        $lib['php-pods-extends'] = [
            'pod' => 'php-pods',
            'internal' => false,
            'protocol' => Transport::Tcp->value,
            'ports' => [
                [
                    'listen' => 9876,
                    'target' => 8080,
                ],
            ],
        ];
    }

    #[Given('a job workspace agent')]
    public function aJobWorkspaceAgent(): void
    {
        $workspace = new class ($this->paasFile) implements JobWorkspaceInterface {
            use ImmutableTrait;

            public function __construct(
                private ?string &$paasFile,
            ) {
            }

            public function setJob(JobUnitInterface $job): JobWorkspaceInterface
            {
                return $this;
            }

            public function clean(): JobWorkspaceInterface
            {
                return $this;
            }

            public function writeFile(FileInterface $file, ?callable $return = null): JobWorkspaceInterface
            {
                return $this;
            }

            public function prepareRepository(CloningAgentInterface $cloningAgent): JobWorkspaceInterface
            {
                return $this;
            }

            public function loadDeploymentIntoConductor(
                ConductorInterface $conductor,
                PromiseInterface $promise
            ): JobWorkspaceInterface {
                if (empty($this->paasFile) || !file_exists($this->paasFile)) {
                    throw new RuntimeException('Error, the paas file was not defined for this test');
                }

                $conf = file_get_contents($this->paasFile);

                $conductor->prepare(
                    $conf,
                    $promise
                );

                return $this;
            }

            public function hasDirectory(string $path, PromiseInterface $promise): JobWorkspaceInterface
            {
                $promise->success();

                return $this;
            }

            public function runInRepositoryPath(callable $callback): JobWorkspaceInterface
            {
                $callback('/foo');

                return $this;
            }
        };

        $this->sfContainer->set(
            JobWorkspaceInterface::class,
            $workspace
        );
    }

    #[Given('a git cloning agent')]
    public function aGitCloningAgent(): void
    {
        $cloningAgent = new class () implements CloningAgentInterface {
            use ImmutableTrait;

            private ?JobWorkspaceInterface $workspace = null;

            public function configure(
                SourceRepositoryInterface $repository,
                JobWorkspaceInterface $workspace
            ): CloningAgentInterface {
                $that = clone $this;

                $that->workspace = $workspace;

                return $that;
            }

            public function run(): CloningAgentInterface
            {
                $this->workspace->prepareRepository($this);

                return $this;
            }

            public function cloningIntoPath(string $jobRootPath, string $repositoryFolder): CloningAgentInterface
            {
                return $this;
            }
        };

        $this->sfContainer->set(
            CloningAgentInterface::class,
            $cloningAgent
        );
    }

    #[Given('a composer hook as hook builder')]
    public function aComposerHookAsHookBuilder(): void
    {
        $hook = new HookMock();

        $hooks = ['composer-8.2' => clone $hook, 'hook-id-BAR' => clone $hook];
        $collection = new readonly class ($hooks) implements HooksCollectionInterface {
            private iterable $hooks;

            public function __construct(iterable $hooks)
            {
                $this->hooks = $hooks;
            }

            public function getIterator(): Traversable
            {
                yield from $this->hooks;
            }
        };

        $this->sfContainer->set(
            HooksCollectionInterface::class,
            $collection
        );
    }

    #[Given('without any hooks path defined')]
    public function withoutAnyHooksPathDefined(): void
    {
        $_ENV['SPACE_HOOKS_COLLECTION_JSON'] = json_encode([], JSON_THROW_ON_ERROR);
    }

    #[Given('composer in several version as hook')]
    public function composerInSeveralVersionAsHook(): void
    {
        $_ENV['SPACE_HOOKS_COLLECTION_JSON'] = json_encode(
            [
                [
                    'name' => 'behat-composer',
                    'type' => 'composer',
                    'command' => [
                        'php',
                        '-f',
                        '/usr/local/bin/composer',
                        '--',
                    ],
                    'timeout' => 240,
                ],
                [
                    'name' => 'behat-composer-8.1',
                    'type' => 'composer',
                    'command' => [
                        'php8.1',
                        '-f',
                        '/usr/local/bin/composer',
                        '--',
                    ],
                    'timeout' => 240,
                ],
                [
                    'name' => 'behat-composer-8.2',
                    'type' => 'composer',
                    'command' => [
                        'php8.2',
                        '-f',
                        '/usr/local/bin/composer',
                        '--',
                    ],
                    'timeout' => 240,
                ],
            ],
            JSON_THROW_ON_ERROR,
        );
    }

    #[When('the hook library is generated')]
    public function theHookLibraryIsGenerated(): void
    {
        $this->hookCollection = $this->sfContainer->get(HooksCollectionInterface::class);
    }

    #[Then('it obtains non empty hooks library with :name key.')]
    public function itObtainsNonEmptyHooksLibraryWithKey(string $name): void
    {
        $hooks = iterator_to_array($this->hookCollection);
        Assert::assertArrayHasKey($name, $hooks);
        Assert::assertArrayHasKey($name . '-8.1', $hooks);
        Assert::assertArrayHasKey($name . '-8.2', $hooks);
    }

    #[Then('it obtains an hooks library without :name key.')]
    public function itObtainsEmptyHooksLibrary(string $name): void
    {
        $hooks = iterator_to_array($this->hookCollection);
        Assert::assertArrayNotHasKey($name, $hooks);
    }
}
