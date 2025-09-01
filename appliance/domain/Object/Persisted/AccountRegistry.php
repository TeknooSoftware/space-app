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

use SensitiveParameter;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\TimestampableInterface;
use Teknoo\East\Common\Object\ObjectTrait;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Contracts\Object\AccountComponentInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountRegistry implements
    IdentifiedObjectInterface,
    TimestampableInterface,
    ImmutableInterface,
    AccountComponentInterface
{
    use ObjectTrait;
    use ImmutableTrait;

    private Account $account;

    private string $registryNamespace;

    private string $registryUrl;

    private string $registryConfigName;

    private string $registryAccountName;

    private string $registryPassword;

    private string $persistentVolumeClaimName = '';

    public function __construct(
        Account $account,
        string $registryNamespace,
        string $registryUrl,
        string $registryAccountName,
        string $registryConfigName,
        #[SensitiveParameter]
        string $registryPassword,
        string $persistentVolumeClaimName,
    ) {
        $this->uniqueConstructorCheck();

        $this->account = $account;
        $this->registryNamespace = $registryNamespace;
        $this->registryUrl = $registryUrl;
        $this->registryConfigName = $registryConfigName;
        $this->registryAccountName = $registryAccountName;
        $this->registryPassword = $registryPassword;
        $this->persistentVolumeClaimName = $persistentVolumeClaimName;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function getRegistryNamespace(): string
    {
        return $this->registryNamespace;
    }

    public function getRegistryUrl(): string
    {
        return $this->registryUrl;
    }

    public function getRegistryConfigName(): string
    {
        return $this->registryConfigName;
    }

    public function getRegistryAccountName(): string
    {
        return $this->registryAccountName;
    }

    public function getRegistryPassword(): string
    {
        return $this->registryPassword;
    }

    public function getPersistentVolumeClaimName(): string
    {
        return $this->persistentVolumeClaimName;
    }

    public function updateRegistry(
        string $registryUrl,
        string $registryAccountName,
        string $registryPassword,
    ): self {
        $that = clone $this;

        $that->registryUrl = $registryUrl;
        $that->registryAccountName = $registryAccountName;
        $that->registryPassword = $registryPassword;

        return $that;
    }

    public function verifyAccessToUser(User $user, PromiseInterface $promise): AccountComponentInterface
    {
        $this->account->verifyAccessToUser($user, $promise);

        return $this;
    }
}
