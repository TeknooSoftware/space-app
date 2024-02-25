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

use DomainException;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Cluster;
use Teknoo\East\Paas\Object\Job;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\AccountWallet;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class JobSetDefaults
{
    public function __construct(
        private ClusterCatalog $catalog,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        Job $job,
        AccountWallet $accountWallet,
    ): self {
        $defaults = [];
        /**
         * @param Cluster[] $clusters
         */
        $defaultsGenerator = function (iterable $clusters) use (&$defaults, $accountWallet): void {
            foreach ($clusters as $cluster) {
                $credential = $accountWallet[$cluster];
                $config = $this->catalog->getCluster($cluster);

                $registryConfigName = $credential?->getRegistryConfigName();
                if (empty($registryConfigName)) {
                    throw new DomainException("Error, there are no registry config name for {$cluster}");
                }

                $defaults = [
                    'oci-registry-config-name' => $registryConfigName,
                    'storage-provider' => $config->storageProvisioner,
                ];

                break;
            }
        };

        $job->visit(['clusters' => $defaultsGenerator]);

        $job->setDefaults($defaults);

        return $this;
    }
}
