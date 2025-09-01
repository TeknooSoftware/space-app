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

namespace Teknoo\Space\Object\Persisted;

use DateTimeInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\TimestampableInterface;
use Teknoo\East\Common\Object\ObjectTrait;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\History;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Contracts\Object\AccountComponentInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountHistory implements
    IdentifiedObjectInterface,
    TimestampableInterface,
    AccountComponentInterface
{
    use ObjectTrait;

    protected ?History $history = null;

    public function __construct(
        private Account $account
    ) {
    }

    /**
     * @param array<string, mixed> $extra
     */
    public function addToHistory(
        string $message,
        DateTimeInterface $date,
        bool $isFinal = false,
        array $extra = []
    ): self {
        $this->setHistory(new History($this->history, $message, $date, $isFinal, $extra));

        return $this;
    }

    public function setHistory(?History $history): self
    {
        $this->history = $history?->limit(150);

        return $this;
    }

    public function passMeYouHistory(callable $callback): self
    {
        if (null === $this->history) {
            return $this;
        }

        $callback($this->history);

        return $this;
    }

    public function verifyAccessToUser(User $user, PromiseInterface $promise): AccountComponentInterface
    {
        $this->account->verifyAccessToUser($user, $promise);

        return $this;
    }
}
