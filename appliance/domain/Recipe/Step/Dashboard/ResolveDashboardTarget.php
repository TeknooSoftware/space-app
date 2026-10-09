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

namespace Teknoo\Space\Recipe\Step\Dashboard;

use BadMethodCallException;
use DomainException;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\DashboardProfileCatalog;
use Teknoo\Space\Object\Config\Exception\UnsupportedClusterTypeException;
use Teknoo\Space\Object\Config\KubernetesCluster;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\DTO\DashboardTarget;
use Teknoo\Space\Service\DashboardAvailability;

use function in_array;
use function trim;

/**
 * Resolves the dashboard to relay for the current user, without reaching it: the cluster, its dashboard's profile,
 * and the credential and the namespace of the user. An administrator uses the cluster's credential on all
 * namespaces, a user the credential of its environment on the environment's namespace only.
 * The transport of the requests is the job of the `DashboardFrameInterface` step.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ResolveDashboardTarget
{
    public function __construct(
        private readonly DashboardProfileCatalog $profileCatalog,
        private readonly DashboardAvailability $dashboardAvailability,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        User $user,
        ClusterCatalog $clusterCatalog,
        string $clusterName,
        ?string $envName = null,
        ?AccountWallet $accountWallet = null,
    ): self {
        $cluster = $clusterCatalog->getCluster($clusterName);
        if (!$cluster instanceof KubernetesCluster) {
            throw new UnsupportedClusterTypeException('Only the dashboard of a Kubernetes cluster can be embedded');
        }

        if (!$this->dashboardAvailability->isAvailable($cluster)) {
            throw new DomainException(message: "No dashboard is available for this cluster", code: 404);
        }

        $token = $cluster->token;
        $namespace = null;

        if (!in_array('ROLE_ADMIN', (array) $user->getRoles(), true)) {
            if (null === $accountWallet) {
                throw new BadMethodCallException(message: "Wallet is mandatory for non admin user", code: 403);
            }

            if (null === $envName) {
                throw new BadMethodCallException(message: "Environment name is mandatory", code: 400);
            }

            $accountEnvironment = $accountWallet->get($cluster->name, $envName);
            if (null === $accountEnvironment) {
                throw new BadMethodCallException(message: "Cluster is not allowed for this user", code: 403);
            }

            $token = $accountEnvironment->getToken();
            $namespace = $accountEnvironment->getNamespace();
        }

        $manager->updateWorkPlan([
            DashboardTarget::class => new DashboardTarget(
                cluster: $cluster,
                profile: $this->profileCatalog->getProfile($cluster->dashboardType),
                token: trim($token),
                clusterName: $clusterName,
                envName: (string) $envName,
                namespace: $namespace,
            ),
        ]);

        return $this;
    }
}
