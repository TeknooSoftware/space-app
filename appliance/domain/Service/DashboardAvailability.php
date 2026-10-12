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

namespace Teknoo\Space\Service;

use Teknoo\Space\Object\Config\ConfigClusterInterface;

use function trim;

/**
 * Tells if the web dashboard of a cluster can be embedded in Space and relayed by it, whatever the cluster's type:
 * a cluster without dashboard type or without dashboard address has no dashboard. The dashboard of a cluster
 * registered by a client (external cluster) is relayed only when the operator allows it
 * (`SPACE_DASHBOARD_EXTERNAL_ENABLED`): its address is supplied by the client, so the relay then also requires it to
 * be served over https by a public host.
 * The dashboard page and the relay share this rule, so a frame is never shown for a dashboard the relay refuses.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class DashboardAvailability
{
    public function __construct(
        private readonly bool $externalDashboardsEnabled = false,
    ) {
    }

    public function isAvailable(ConfigClusterInterface $cluster): bool
    {
        return '' !== trim($cluster->dashboardType)
            && '' !== trim($cluster->dashboardAddress)
            && (!$cluster->isExternal || $this->externalDashboardsEnabled);
    }
}
