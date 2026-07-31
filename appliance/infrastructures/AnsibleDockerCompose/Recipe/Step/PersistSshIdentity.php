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
use Teknoo\Space\Object\Config\ConfigClusterInterface;
use Teknoo\Space\Object\Config\DockerComposeCluster;
use Teknoo\Space\Object\Config\Exception\UnsupportedClusterTypeException;

use function strtolower;

/**
 * Docker-compose provisioning step: stage the SSH identity (key + known_hosts, key-only/rootless — no password)
 * and the compose namespace onto the workplan so the reused {@see \Teknoo\Space\Recipe\Step\AccountEnvironment\
 * PersistEnvironment} writes them onto the `AccountEnvironment` (reusing its existing `client_key` /
 * `ca_certificate` fields — no schema change). No Kubernetes API is touched.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class PersistSshIdentity
{
    public function __invoke(
        ManagerInterface $manager,
        ConfigClusterInterface $clusterConfig,
        string $accountNamespace,
        string $envName,
    ): self {
        if (!$clusterConfig instanceof DockerComposeCluster) {
            throw new UnsupportedClusterTypeException('This step only supports docker-compose clusters');
        }

        $namespace = strtolower($accountNamespace . '-' . $envName);

        $manager->updateWorkPlan([
            'kubeNamespace' => $namespace,
            'serviceName' => '',
            'roleName' => '',
            'roleBindingName' => '',
            'caCertificate' => $clusterConfig->caCertificate,
            'token' => '',
            'clientKey' => $clusterConfig->clientKey,
        ]);

        return $this;
    }
}
