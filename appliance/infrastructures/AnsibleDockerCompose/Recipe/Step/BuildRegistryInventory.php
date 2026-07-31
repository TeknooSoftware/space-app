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

use DomainException;
use League\Flysystem\Filesystem;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\DockerComposeCluster;
use Teknoo\Space\Object\Config\Exception\UnsupportedClusterTypeException;

use function parse_url;
use function uniqid;

/**
 * Build a single-host Ansible inventory from the docker-compose registry cluster's SSH management address
 * (`ssh://user@host:port`) and write it under the worker tmp dir through a Flysystem adapter; the absolute path
 * is staged on the workplan as `inventoryPath`. The East PaaS runner factory materialises the SSH key — this
 * helper only owns the inventory file.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class BuildRegistryInventory
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly string $tmpDir,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        ClusterCatalog $clusterCatalog,
    ): self {
        $cluster = $clusterCatalog->getClusterForRegistry();

        if (!$cluster instanceof DockerComposeCluster) {
            throw new UnsupportedClusterTypeException('This step only supports docker-compose clusters');
        }

        $parts = parse_url($cluster->masterAddress);

        if (false === $parts || empty($parts['host'])) {
            throw new DomainException(
                "Invalid SSH address '{$cluster->masterAddress}': unable to parse the host"
            );
        }

        $host = $parts['host'];
        $port = $parts['port'] ?? 22;
        $user = $parts['user'] ?? null;

        $line = $host;
        if (null !== $user) {
            $line .= ' ansible_user=' . $user;
        }
        $line .= ' ansible_host=' . $host . ' ansible_port=' . $port;

        $inventory = "[registry_host]\n" . $line . "\n";

        $fileName = 'registry-inventory-' . uniqid() . '.ini';
        $this->filesystem->write($fileName, $inventory);

        $manager->updateWorkPlan(['inventoryPath' => $this->tmpDir . '/' . $fileName]);

        return $this;
    }
}
