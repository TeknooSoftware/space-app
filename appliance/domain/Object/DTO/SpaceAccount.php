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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Object\DTO;

use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\Persisted\AccountData;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SpaceAccount implements IdentifiedObjectInterface
{
    /**
     * @param iterable<AccountPersistedVariable>|AccountPersistedVariable[] $variables
     */
    public function __construct(
        public Account $account = new Account(),
        public ?AccountData $accountData = null,
        public iterable $variables = [],
    ) {
        if (null === $this->accountData) {
            $this->accountData = new AccountData($this->account);
        }
    }

    public function getId(): string
    {
        return (string) $this->account->getId();
    }

    public function __toString(): string
    {
        return (string) $this->account;
    }
}
