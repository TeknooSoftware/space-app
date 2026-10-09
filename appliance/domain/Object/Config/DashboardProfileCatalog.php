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

namespace Teknoo\Space\Object\Config;

use DomainException;
use IteratorAggregate;
use Traversable;

/**
 * Profiles of the Kubernetes web dashboards supported by Space, by type. A cluster without dashboard type uses the
 * default profile.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements IteratorAggregate<string, DashboardProfile>
 */
class DashboardProfileCatalog implements IteratorAggregate
{
    /**
     * @param array<string, DashboardProfile> $profiles
     */
    public function __construct(
        private readonly array $profiles,
        private readonly string $defaultType,
    ) {
        if (!isset($this->profiles[$this->defaultType])) {
            throw new DomainException(
                "The default dashboard type {$this->defaultType} is not available in the catalog",
            );
        }
    }

    public function getProfile(?string $type = null): DashboardProfile
    {
        if (null === $type || '' === $type) {
            $type = $this->defaultType;
        }

        if (!isset($this->profiles[$type])) {
            throw new DomainException("The dashboard type {$type} is not available in the catalog");
        }

        return $this->profiles[$type];
    }

    public function getIterator(): Traversable
    {
        yield from $this->profiles;
    }
}
