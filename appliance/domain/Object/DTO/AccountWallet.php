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

namespace Teknoo\Space\Object\DTO;

use ArrayAccess;
use BadMethodCallException;
use IteratorAggregate;
use Teknoo\East\Paas\Object\Cluster;
use Teknoo\Space\Object\Persisted\AccountCredential;
use Traversable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements IteratorAggregate<AccountCredential>
 * @implements ArrayAccess<string, AccountCredential>
 */
class AccountWallet implements ArrayAccess, IteratorAggregate
{
    /**
     * @param iterable<AccountCredential>|AccountCredential[] $credentials
     */
    public function __construct(
        private readonly iterable $credentials
    ) {
    }

    /**
     * @return Traversable<AccountCredential>
     */
    public function getIterator(): Traversable
    {
        yield from $this->credentials;
    }

    public function offsetExists(mixed $offset): bool
    {
        return null !== $this->offsetGet($offset);
    }

    /**
     * @param string|Cluster $offset
     */
    public function offsetGet(mixed $offset): mixed
    {
        if ($offset instanceof Cluster) {
            $offset = (string) $offset;
        }

        foreach ($this->credentials as $credential) {
            if ($credential->getClusterName() === $offset) {
                return $credential;
            }
        }

        return null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException('Method not available');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException('Method not available');
    }
}
