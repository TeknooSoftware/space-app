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

use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Infrastructures\AnsibleDockerCompose\PlaybookRunner;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\DockerComposeCluster;
use Teknoo\Space\Object\Config\Exception\UnsupportedClusterTypeException;
use Throwable;

/**
 * Run the registry provisioning playbook on the remote Docker host through the `PlaybookRunner`, which writes the
 * single-host inventory and removes it once the playbook has run. SSH is key-only and rootless — the
 * `ClusterCredentials` carry only the private key (+ optional known_hosts / username), never a password.
 * Success/failure is routed through a `Promise` to the workplan / the manager's error channel.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class RunRegistryPlaybook
{
    public function __construct(
        private readonly PlaybookRunner $playbookRunner,
        private readonly string $playbookPath,
    ) {
    }

    /**
     * @param array<string, mixed> $extraVars
     */
    public function __invoke(
        ManagerInterface $manager,
        ClusterCatalog $clusterCatalog,
        array $extraVars,
        ?string $registryClusterName = null,
    ): self {
        //The registry cluster is resolved once per task by `SelectRegistryCluster`; falls back to the
        //first cluster supporting the registry when the task did not resolve it.
        $cluster = $clusterCatalog->getClusterForRegistry($registryClusterName);

        if (!$cluster instanceof DockerComposeCluster) {
            throw new UnsupportedClusterTypeException('This step only supports docker-compose clusters');
        }

        /** @var Promise<array<string, mixed>|string, mixed, mixed> $promise */
        $promise = new Promise(
            onSuccess: static function (array|string $result) use ($manager): void {
                $manager->updateWorkPlan(['registryInstallResult' => $result]);
            },
            onFail: static function (Throwable $error) use ($manager): void {
                $manager->error($error);
            },
        );

        $this->playbookRunner->run(
            $this->playbookPath,
            $cluster->masterAddress,
            $cluster->getCredentials(),
            $extraVars,
            $promise,
        );

        return $this;
    }
}
