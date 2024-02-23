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

use SensitiveParameter;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\TimestampableInterface;
use Teknoo\East\Common\Object\ObjectTrait;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountCredential implements IdentifiedObjectInterface, TimestampableInterface, ImmutableInterface
{
    use ObjectTrait;
    use ImmutableTrait;

    private Account $account;

    private string $clusterName;

    private string $registryUrl;

    private string $registryConfigName;

    private string $registryAccountName;

    private string $registryPassword;

    private string $serviceAccountName;

    private string $roleName;

    private string $roleBindingName;

    private string $caCertificate = '';

    private string $clientCertificate = '';

    private string $clientKey = '';

    private string $token;

    private string $persistentVolumeClaimName = '';

    public function __construct(
        Account $account,
        string $clusterName,
        string $registryUrl,
        string $registryAccountName,
        string $registryConfigName,
        #[SensitiveParameter]
        string $registryPassword,
        string $serviceAccountName,
        string $roleName,
        string $roleBindingName,
        string $caCertificate,
        string $clientCertificate,
        string $clientKey,
        string $token,
        string $persistentVolumeClaimName,
    ) {
        $this->uniqueConstructorCheck();

        $this->account = $account;
        $this->clusterName = $clusterName;
        $this->registryUrl = $registryUrl;
        $this->registryConfigName = $registryConfigName;
        $this->registryAccountName = $registryAccountName;
        $this->registryPassword = $registryPassword;
        $this->serviceAccountName = $serviceAccountName;
        $this->roleName = $roleName;
        $this->roleBindingName = $roleBindingName;
        $this->caCertificate = $caCertificate;
        $this->clientCertificate = $clientCertificate;
        $this->clientKey = $clientKey;
        $this->token = $token;
        $this->persistentVolumeClaimName = $persistentVolumeClaimName;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function getClusterName(): string
    {
        return $this->clusterName;
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

    public function getServiceAccountName(): string
    {
        return $this->serviceAccountName;
    }

    public function getRoleName(): string
    {
        return $this->roleName;
    }

    public function getRoleBindingName(): string
    {
        return $this->roleBindingName;
    }

    public function getCaCertificate(): string
    {
        return $this->caCertificate;
    }

    public function getClientCertificate(): string
    {
        return $this->clientCertificate;
    }

    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    public function getToken(): string
    {
        return $this->token;
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
}
