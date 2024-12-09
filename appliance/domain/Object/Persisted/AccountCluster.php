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
 * @link        http://https://teknoo.software/applications/space Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Object\Persisted;

use SensitiveParameter;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\SluggableInterface;
use Teknoo\East\Common\Contracts\Object\TimestampableInterface;
use Teknoo\East\Common\Contracts\Object\VisitableInterface;
use Teknoo\East\Common\Object\ObjectTrait;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Object\VisitableTrait;
use Teknoo\East\Common\Service\FindSlugService;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Contracts\Object\AccountComponentInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements SluggableInterface<IdentifiedObjectInterface>
 */
class AccountCluster implements
    IdentifiedObjectInterface,
    TimestampableInterface,
    ImmutableInterface,
    SluggableInterface,
    VisitableInterface,
    AccountComponentInterface
{
    use ObjectTrait;
    use ImmutableTrait;
    use VisitableTrait {
        VisitableTrait::runVisit as realRunVisit;
    }

    public function __construct(
        private readonly Account $account,
        private readonly string $name,
        private string $slug,
        private readonly string $type,
        private readonly string $masterAddress,
        private readonly string $storageProvisioner,
        private readonly string $dashboardAddress,
        private readonly string $caCertificate,
        #[SensitiveParameter]
        private readonly string $token,
        private readonly bool $supportRegistry,
        private readonly ?string $registryUrl,
        private readonly bool $useHnc,
    ) {
        $this->uniqueConstructorCheck();
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMasterAddress(): string
    {
        return $this->masterAddress;
    }

    public function getStorageProvisioner(): string
    {
        return $this->storageProvisioner;
    }

    public function getDashboardAddress(): string
    {
        return $this->dashboardAddress;
    }

    public function getCaCertificate(): string
    {
        return $this->caCertificate;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getRegistryUrl(): ?string
    {
        return $this->registryUrl;
    }

    public function isSupportRegistry(): bool
    {
        return $this->supportRegistry;
    }

    public function isUseHnc(): bool
    {
        return $this->useHnc;
    }

    public function prepareSlugNear(
        LoaderInterface $loader,
        FindSlugService $findSlugService,
        string $slugField
    ): SluggableInterface {
        $slugValue = $this->getSlug();
        if (empty($slugValue)) {
            $slugValue = $this->getName();
        }

        $findSlugService->process(
            $loader,
            $slugField,
            $this,
            [
                $slugValue
            ]
        );

        return $this;
    }

    public function setSlug(string $slug): SluggableInterface
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @param array<string, callable> $visitors
     */
    private function runVisit(array &$visitors): void
    {
        $caseMapping = [
            'master_address' => 'masterAddress',
            'storage_provisioner' => 'storage_provisioner',
            'dashboard_address' => 'dashboardAddress',
            'ca_certificate' => 'caCertificate',
            'support_registry' => 'supportRegistry',
            'registry_url' => 'registryUrl',
        ];

        foreach ($caseMapping as $snake => $camel) {
            if (isset($visitors[$snake])) {
                $visitors[$camel] = $visitors[$snake];
                unset($visitors[$snake]);
            }
        }

        $this->realRunVisit($visitors);
    }

    public function verifyAccessToUser(User $user, PromiseInterface $promise): AccountComponentInterface
    {
        $this->account->verifyAccessToUser($user, $promise);

        return $this;
    }
}
