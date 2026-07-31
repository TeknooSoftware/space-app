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

/**
 * Common cluster-configuration contract shared by every cluster type (Kubernetes, docker-compose, ...).
 * Exposes only the type-agnostic members; type-specific members (Kubernetes clients, storage provisioner,
 * token, ...) live on the concrete implementations.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
interface ConfigClusterInterface
{
    public string $name { get; }

    public string $sluggyName { get; }

    public string $type { get; }

    public string $masterAddress { get; }

    public string $dashboardAddress { get; }

    public bool $supportRegistry { get; }

    public bool $useHnc { get; }

    public bool $isExternal { get; }
}
