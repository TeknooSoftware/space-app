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

namespace Teknoo\Space\Object\Config;

use DomainException;
use IteratorAggregate;
use Teknoo\East\Paas\Object\Cluster as EastCluster;
use Teknoo\Kubernetes\Client;
use Traversable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements IteratorAggregate<Cluster>
 */
class ClusterCatalog implements IteratorAggregate
{
    /**
     * @param array<string, Cluster> $clusters
     * @param array<string, string> $aliases
     */
    public function __construct(
        private readonly array $clusters,
        private readonly array $aliases,
    ) {
    }

    public function getClusterForRegistry(): Cluster
    {
        foreach ($this->clusters as $cluster) {
            if ($cluster->supportRegistry) {
                return $cluster;
            }
        }

        throw new DomainException("Missing cluster configuration able to support privates registries");
    }

    public function getCluster(string|EastCluster $name): Cluster
    {
        if ($name instanceof EastCluster) {
            $name = (string) $name;
        }

        if (isset($this->aliases[$name])) {
            $name = $this->aliases[$name];
        }

        if (!isset($this->clusters[$name])) {
            throw new DomainException("Cluster {$name} is not available in the catalog");
        }

        return $this->clusters[$name];
    }

    public function getIterator(): Traversable
    {
        yield from $this->clusters;
    }
}
