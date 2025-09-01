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
use Teknoo\Space\Object\DTO\AccountEnvironmentResume;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountEnvironment implements
    IdentifiedObjectInterface,
    TimestampableInterface,
    ImmutableInterface,
    AccountComponentInterface
{
    use ObjectTrait;
    use ImmutableTrait;

    private Account $account;

    private string $clusterName;

    private string $envName;

    private string $namespace;

    private string $serviceAccountName;

    private string $roleName;

    private string $roleBindingName;

    private string $caCertificate = '';

    private string $clientCertificate = '';

    private string $clientKey = '';

    private string $token;

    /**
     * @var array<string, mixed>
     */
    private ?array $metadata;

    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        Account $account,
        string $clusterName,
        string $envName,
        string $namespace,
        string $serviceAccountName,
        string $roleName,
        string $roleBindingName,
        string $caCertificate,
        string $clientCertificate,
        #[SensitiveParameter]
        string $clientKey,
        #[SensitiveParameter]
        string $token,
        ?array $metadata,
    ) {
        $this->uniqueConstructorCheck();

        $this->account = $account;
        $this->clusterName = $clusterName;
        $this->namespace = $namespace;
        $this->envName = $envName;
        $this->serviceAccountName = $serviceAccountName;
        $this->roleName = $roleName;
        $this->roleBindingName = $roleBindingName;
        $this->caCertificate = $caCertificate;
        $this->clientCertificate = $clientCertificate;
        $this->clientKey = $clientKey;
        $this->token = $token;
        $this->metadata = $metadata;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function getClusterName(): string
    {
        return $this->clusterName;
    }

    public function getEnvName(): string
    {
        return $this->envName;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
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

    /**
     * @return array<string, mixed>|null
     */
    public function getAllMetaData(): ?array
    {
        return $this->metadata;
    }

    public function getMetaData(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    public function verifyAccessToUser(User $user, PromiseInterface $promise): AccountComponentInterface
    {
        $this->account->verifyAccessToUser($user, $promise);

        return $this;
    }

    public function resume(): AccountEnvironmentResume
    {
        return new AccountEnvironmentResume(
            clusterName: $this->getClusterName(),
            envName: $this->getEnvName(),
            accountEnvironmentId: $this->getId(),
        );
    }
}
