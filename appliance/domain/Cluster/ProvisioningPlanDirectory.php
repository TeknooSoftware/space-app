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

namespace Teknoo\Space\Cluster;

use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Space\Cluster\Contract\ProvisioningPlanDirectoryInterface;
use Teknoo\Space\Object\Config\Exception\UnsupportedClusterTypeException;

/**
 * Registry of provisioning plan sets keyed by cluster `type`. Mirrors the vendored
 * {@see \Teknoo\East\Paas\Cluster\Directory} (type → driver) but returns Space provisioning plans. The
 * Kubernetes set holds the existing plan instances unchanged; other types (docker-compose) register their own.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ProvisioningPlanDirectory implements ProvisioningPlanDirectoryInterface
{
    /**
     * @var array<string, ProvisioningPlanSet>
     */
    private array $planSets = [];

    public function register(string $type, ProvisioningPlanSet $planSet): self
    {
        $this->planSets[$type] = $planSet;

        return $this;
    }

    private function getPlanSet(string $type): ProvisioningPlanSet
    {
        return $this->planSets[$type]
            ?? throw new UnsupportedClusterTypeException(
                "No provisioning plan set registered for cluster type '{$type}'"
            );
    }

    public function environmentInstall(string $type): EditablePlanInterface
    {
        return $this->getPlanSet($type)->environmentInstall;
    }

    public function environmentReinstall(string $type): EditablePlanInterface
    {
        return $this->getPlanSet($type)->environmentReinstall;
    }

    public function refreshQuota(string $type): EditablePlanInterface
    {
        return $this->getPlanSet($type)->refreshQuota;
    }

    public function registryInstall(string $type): EditablePlanInterface
    {
        return $this->getPlanSet($type)->registryInstall;
    }

    public function registryReinstall(string $type): EditablePlanInterface
    {
        return $this->getPlanSet($type)->registryReinstall;
    }
}
