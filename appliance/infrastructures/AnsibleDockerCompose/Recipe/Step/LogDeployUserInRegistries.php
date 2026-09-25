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

namespace Teknoo\Space\Infrastructures\AnsibleDockerCompose\Recipe\Step;

use DateTimeInterface;
use SensitiveParameter;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Infrastructures\AnsibleDockerCompose\PlaybookRunner;
use Teknoo\Space\Object\Config\ConfigClusterInterface;
use Teknoo\Space\Object\Config\DockerComposeCluster;
use Teknoo\Space\Object\Config\Exception\UnsupportedClusterTypeException;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Object\Persisted\AccountRegistry;
use Throwable;

use function array_column;

/**
 * Docker Compose counterpart of the Kubernetes `CreateDockerSecret`: logs the deploy user of the environment's
 * Docker host in on the account registry and on the global Space registry (when one is configured), so the
 * `docker compose up` run later by the East PaaS deploy playbook can pull the images. The login is done by the
 * self-contained `registries-login.yml` playbook, run over SSH by the `PlaybookRunner` with the cluster
 * credentials, the same SSH user and the same single-host inventory as the deployment. The history is persisted by
 * the task plan's `UpdateAccountHistory`.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LogDeployUserInRegistries
{
    public function __construct(
        private readonly PlaybookRunner $playbookRunner,
        private readonly string $playbookPath,
        private readonly DatesService $datesService,
        private readonly bool $preferRealDate,
        private readonly string $spaceRegistryUrl,
        private readonly string $spaceRegistryUsername,
        #[SensitiveParameter]
        private readonly string $spaceRegistryPwd,
    ) {
    }

    /**
     * @return array<int, array{host: string, username: string, password: string}>
     */
    private function listRegistries(?AccountRegistry $accountRegistry): array
    {
        $registries = [];
        if (null !== $accountRegistry) {
            $registries[] = [
                'host' => $accountRegistry->getRegistryUrl(),
                'username' => $accountRegistry->getRegistryAccountName(),
                'password' => $accountRegistry->getRegistryPassword(),
            ];
        }

        //The global Space registry is optional, a platform without one leaves its url or its username empty
        if ('' !== $this->spaceRegistryUrl && '' !== $this->spaceRegistryUsername) {
            $registries[] = [
                'host' => $this->spaceRegistryUrl,
                'username' => $this->spaceRegistryUsername,
                'password' => $this->spaceRegistryPwd,
            ];
        }

        return $registries;
    }

    public function __invoke(
        ManagerInterface $manager,
        ConfigClusterInterface $clusterConfig,
        AccountHistory $accountHistory,
        string $envName,
        string $clusterName,
        ?AccountRegistry $accountRegistry = null,
    ): self {
        if (!$clusterConfig instanceof DockerComposeCluster) {
            throw new UnsupportedClusterTypeException('This step only supports docker-compose clusters');
        }

        $registries = $this->listRegistries($accountRegistry);
        if (empty($registries)) {
            return $this;
        }

        $hosts = array_column($registries, 'host');

        /** @var Promise<array<string, mixed>|string, mixed, mixed> $promise */
        $promise = new Promise(
            onSuccess: function () use ($accountHistory, $envName, $clusterName, $hosts): void {
                $this->datesService->passMeTheDate(
                    static function (
                        DateTimeInterface $dateTime,
                    ) use (
                        $accountHistory,
                        $envName,
                        $clusterName,
                        $hosts,
                    ): void {
                        $accountHistory->addToHistory(
                            'teknoo.space.text.account.docker_compose.registries_login',
                            $dateTime,
                            false,
                            [
                                'environment' => $envName,
                                'cluster' => $clusterName,
                                'registries' => $hosts,
                            ],
                        );
                    },
                    $this->preferRealDate,
                );
            },
            onFail: static function (#[SensitiveParameter] Throwable $error) use ($manager): void {
                $manager->error($error);
            },
        );

        $this->playbookRunner->run(
            $this->playbookPath,
            $clusterConfig->masterAddress,
            $clusterConfig->getCredentials(),
            ['registries' => $registries],
            $promise,
        );

        return $this;
    }
}
