<?php

/*
 * Teknoo Space.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Recipe\Step\Job;

use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Cluster;
use Teknoo\East\Paas\Object\Job;
use Teknoo\Space\Object\DTO\NewJob;
use Teknoo\Space\Object\Persisted\AccountRegistry;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class JobSetDefaults
{
    public function __invoke(
        ManagerInterface $manager,
        Job $job,
        AccountRegistry $accountRegistry,
        NewJob $newJob,
    ): self {
        $defaults = [
            'oci-registry-config-name' => $accountRegistry->getRegistryConfigName(),
        ];

        /**
         * @param Cluster[] $clusters
         */
        $defaultsGenerator = function (iterable $clusters) use (&$defaults, $newJob): void {
            foreach ($clusters as $cluster) {
                if ($cluster->isLocked()) {
                    $clusterName = (string) $cluster;
                    if (isset($newJob->storageProvisionerPerCluster[$clusterName])) {
                        $provisioner = $newJob->storageProvisionerPerCluster[$clusterName];
                        $defaults['clusters'][$clusterName]['storage-provider'] = $provisioner;
                    }
                }
            }
        };

        $job->visit('clusters', $defaultsGenerator);

        $job->setDefaults($defaults);

        return $this;
    }
}
