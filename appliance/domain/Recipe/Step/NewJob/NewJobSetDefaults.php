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

namespace Teknoo\Space\Recipe\Step\NewJob;

use Teknoo\East\Paas\Object\Cluster;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\NewJob;
use Teknoo\Space\Object\DTO\SpaceProject;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class NewJobSetDefaults
{
    public function __construct(
        private ClusterCatalog $catalog,
    ) {
    }

    public function __invoke(
        SpaceProject $project,
        NewJob $newJob,
    ): self {

        $project->project->visit(['clusters' => function (iterable $clusters) use ($newJob): void {
            /** @var Cluster[] $clusters */
            foreach ($clusters as $cluster) {
                if ($cluster->isLocked()) {
                    $config = $this->catalog->getCluster($cluster);

                    $newJob->storageProvisionerPerCluster[$config->name] = $config->storageProvisioner;
                }
            }
        }]);

        return $this;
    }
}
