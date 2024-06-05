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

namespace Teknoo\Space\Recipe\Step\Project;

use DomainException;
use LogicException;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Cluster;
use Teknoo\East\Paas\Object\ClusterCredentials;
use Teknoo\East\Paas\Object\Environment;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\DTO\SpaceProject;
use Throwable;

use function is_array;
use function iterator_to_array;
use function sprintf;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AddManagedEnvironmentToProject
{
    public function __construct(
        private ClusterCatalog $catalog,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        SpaceProject $spaceProject,
        AccountWallet $accountWallet,
    ): AddManagedEnvironmentToProject {
        if (
            empty($spaceProject->addClusterEnv)
            && empty(!empty($spaceProject->addClusterName))
        ) {
            return $this;
        }

        if (
            empty($spaceProject->addClusterEnv)
            || empty(!empty($spaceProject->addClusterName))
        ) {
            $manager->error(
                new LogicException(
                    'Cluster env and cluster name are both required to add a managed environment to a project'
                )
            );

            return $this;
        }

        $accountEnv = $accountWallet->get($spaceProject->addClusterName, $spaceProject->addClusterEnv);

        if (!$accountEnv) {
            $manager->error(
                new DomainException(
                    sprintf(
                        "Account env `%s` with `%s` is not available for this account ",
                        $spaceProject->addClusterName,
                        $spaceProject->addClusterEnv,
                    )
                )
            );

            return $this;
        }

        try {
            $clusterConfig = $this->catalog->getCluster($spaceProject->addClusterName);

            $newCluster = new Cluster();
            $newCluster->setName($spaceProject->addClusterName);
            $newCluster->setEnvironment(new Environment($spaceProject->addClusterEnv));
            $newCluster->setType($clusterConfig->type);
            $newCluster->useHierarchicalNamespaces($clusterConfig->useHnc);
            $newCluster->setAddress($clusterConfig->masterAddress);
            $newCluster->setLocked(true);
            $newCluster->setProject($spaceProject->project);
            $newCluster->setNamespace($accountEnv->getNamespace());
            $newCluster->setIdentity(
                new ClusterCredentials(
                    caCertificate: $accountEnv->getCaCertificate(),
                    clientCertificate: $accountEnv->getClientCertificate(),
                    clientKey: $accountEnv->getClientKey(),
                    token: $accountEnv->getToken(),
                ),
            );

            $spaceProject->project->visit(
                visitors: 'clusters',
                callable: static function (iterable $clusters) use ($newCluster, $spaceProject): void {
                    if (!is_array($clusters)) {
                        $clusters = iterator_to_array($clusters);
                    }

                    $clusters[] = $newCluster;
                    $spaceProject->project->setClusters($clusters);
                },
            );
        } catch (Throwable $error) {
            $manager->error($error);
        }

        return $this;
    }
}
