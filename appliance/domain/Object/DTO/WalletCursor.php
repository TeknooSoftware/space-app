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

use Iterator;
use Teknoo\Space\Object\Persisted\AccountEnvironment;

/**
 * Position of a loop over an account's environments wallet, kept in the workplan (so per execution) by
 * `StartLoopingOnWallet` / `EndLoopingOnWallet`. Steps are shared singletons reused by long-running workers, so
 * the iteration state must never live in a step instance.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class WalletCursor
{
    /**
     * @var Iterator<AccountEnvironment>
     */
    private readonly Iterator $iterator;

    public function __construct(AccountWallet $wallet)
    {
        $this->iterator = (static fn (): Iterator => yield from $wallet)();
    }

    public function valid(): bool
    {
        return $this->iterator->valid();
    }

    public function current(): AccountEnvironment
    {
        return $this->iterator->current();
    }

    public function next(): self
    {
        $this->iterator->next();

        return $this;
    }
}
