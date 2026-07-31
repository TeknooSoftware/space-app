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

namespace Teknoo\Space\Infrastructures\Recipe\Bowl;

use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Bowl\RecipeBowl;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookingSupervisorInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Space\Cluster\Contract\ProvisioningPlanDirectoryInterface;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\Exception\UnsupportedClusterTypeException;

use function is_string;

/**
 * Runtime type-aware provisioning bowl. `RecipeBowl`'s recipe is fixed at container-build time, but the target
 * cluster `type` is only known per request. This bowl reads the current cluster (from the workplan's
 * `clusterCatalog` + `clusterName`), resolves the provisioning plan for that `type` and role via the
 * {@see ProvisioningPlanDirectoryInterface}, and delegates to an inner {@see RecipeBowl}. For `kubernetes` the
 * directory returns the existing Kubernetes plan instance, so the K8s path is byte-for-byte unchanged.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ProvisioningPlanBowl implements BowlInterface
{
    use ImmutableTrait;

    public const string ROLE_ENVIRONMENT_INSTALL = 'environmentInstall';

    public const string ROLE_ENVIRONMENT_REINSTALL = 'environmentReinstall';

    public const string ROLE_REFRESH_QUOTA = 'refreshQuota';

    public const string ROLE_REGISTRY_INSTALL = 'registryInstall';

    public const string ROLE_REGISTRY_REINSTALL = 'registryReinstall';

    public function __construct(
        private readonly ProvisioningPlanDirectoryInterface $directory,
        private readonly string $role,
        private readonly int $repeat = 0,
    ) {
        $this->uniqueConstructorCheck();
    }

    /**
     * @param array<string, mixed> $workPlan
     */
    public function execute(
        ChefInterface $chef,
        array &$workPlan,
        ?CookingSupervisorInterface $cookingSupervisor = null,
    ): BowlInterface {
        $catalog = $workPlan['clusterCatalog'] ?? null;
        $clusterName = $workPlan['clusterName'] ?? null;

        if (!$catalog instanceof ClusterCatalog) {
            throw new UnsupportedClusterTypeException(
                'Unable to resolve the cluster type: missing clusterCatalog in the work plan'
            );
        }

        // Environment-scoped roles carry a clusterName; account-scoped roles (registry install/reinstall,
        // quota refresh) do not, so fall back to the account's registry cluster to resolve the type.
        if (is_string($clusterName)) {
            $cluster = $catalog->getCluster($clusterName);
        } else {
            $cluster = $catalog->getClusterForRegistry();
        }

        $type = $cluster->type;

        $plan = $this->resolvePlan($type);

        new RecipeBowl($plan, $this->repeat)->execute($chef, $workPlan, $cookingSupervisor);

        return $this;
    }

    private function resolvePlan(string $type): EditablePlanInterface
    {
        return match ($this->role) {
            self::ROLE_ENVIRONMENT_INSTALL => $this->directory->environmentInstall($type),
            self::ROLE_ENVIRONMENT_REINSTALL => $this->directory->environmentReinstall($type),
            self::ROLE_REFRESH_QUOTA => $this->directory->refreshQuota($type),
            self::ROLE_REGISTRY_INSTALL => $this->directory->registryInstall($type),
            self::ROLE_REGISTRY_REINSTALL => $this->directory->registryReinstall($type),
            default => throw new UnsupportedClusterTypeException(
                "Unknown provisioning role '{$this->role}'"
            ),
        };
    }
}
