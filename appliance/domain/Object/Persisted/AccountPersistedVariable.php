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

namespace Teknoo\Space\Object\Persisted;

use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\TimestampableInterface;
use Teknoo\East\Foundation\Normalizer\Object\NormalizableInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Space\Contracts\Object\EncryptableVariableInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountPersistedVariable implements
    IdentifiedObjectInterface,
    TimestampableInterface,
    ImmutableInterface,
    EncryptableVariableInterface,
    NormalizableInterface
{
    use PersistedVariableTrait;

    private Account $account;

    public function __construct(
        Account $account,
        ?string $id,
        string $name,
        ?string $value,
        string $envName,
        bool $secret,
        ?string $encryptionAlgorithm,
        bool $needEncryption = false,
    ) {
        $this->uniqueConstructorCheck();

        $this->id = $id;
        $this->account = $account;
        $this->name = $name;
        $this->value = $value;
        $this->envName = $envName;
        $this->secret = $secret;
        $this->encryptionAlgorithm = $encryptionAlgorithm;
        $this->needEncryption = $needEncryption;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }
}
