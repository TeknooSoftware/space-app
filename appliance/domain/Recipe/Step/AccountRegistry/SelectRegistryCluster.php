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

namespace Teknoo\Space\Recipe\Step\AccountRegistry;

use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Persisted\AccountRegistry;

/**
 * Resolves, once per provisioning task, the cluster hosting the account's registry, and publishes its canonical
 * name in the work plan under `registryClusterName`.
 *
 * An account registry is bound to the cluster it was installed on: its URL, its namespace and its persistent
 * volume all live there. So a reinstall must target the recorded cluster, not whatever cluster currently comes
 * first in the catalog. A registry created before the cluster name was recorded (or a fresh install, where no
 * registry exists yet) falls back to the first cluster declaring the registry support, and that name is then
 * persisted by `PersistRegistryCredential`.
 *
 * A recorded cluster missing from the catalog raises a `DomainException`, reported in the account history by
 * `AccountTaskErrorHandler`: moving a registry from one cluster to another is never done silently.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SelectRegistryCluster
{
    public function __invoke(
        ManagerInterface $manager,
        ClusterCatalog $clusterCatalog,
        ?AccountRegistry $accountRegistry = null,
    ): self {
        $cluster = $clusterCatalog->getClusterForRegistry($accountRegistry?->getClusterName());

        $manager->updateWorkPlan([
            'registryClusterName' => $cluster->name,
        ]);

        return $this;
    }
}
