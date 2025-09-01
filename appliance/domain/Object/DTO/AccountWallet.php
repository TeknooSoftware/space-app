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

namespace Teknoo\Space\Object\DTO;

use IteratorAggregate;
use Teknoo\East\Paas\Object\Cluster;
use Teknoo\East\Paas\Object\Environment;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Traversable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements IteratorAggregate<AccountEnvironment>
 */
class AccountWallet implements IteratorAggregate
{
    /**
     * @param iterable<AccountEnvironment>|AccountEnvironment[] $credentials
     */
    public function __construct(
        private readonly iterable $credentials
    ) {
    }

    /**
     * @return Traversable<AccountEnvironment>
     */
    public function getIterator(): Traversable
    {
        yield from $this->credentials;
    }

    public function has(string|Cluster $cluster, string|Environment $environment): bool
    {
        return null !== $this->get($cluster, $environment);
    }

    public function get(string|Cluster $cluster, string|Environment $environment): ?AccountEnvironment
    {
        $cluster = (string) $cluster;
        $environment = (string) $environment;

        foreach ($this->credentials as $credential) {
            if (
                $credential->getClusterName() === $cluster
                && $credential->getEnvName() === $environment
            ) {
                return $credential;
            }
        }

        return null;
    }
}
