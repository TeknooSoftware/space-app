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
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\SluggableInterface;
use Teknoo\East\Common\Contracts\Object\TimestampableInterface;
use Teknoo\East\Common\Contracts\Object\VisitableInterface;
use Teknoo\East\Common\Object\ObjectTrait;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Object\VisitableTrait;
use Teknoo\East\Common\Service\FindSlugService;
use Teknoo\East\Foundation\Normalizer\Object\AutoTrait;
use Teknoo\East\Foundation\Normalizer\Object\ClassGroup;
use Teknoo\East\Foundation\Normalizer\Object\Normalize;
use Teknoo\East\Foundation\Normalizer\Object\NormalizableInterface;
use Teknoo\East\Paas\Infrastructures\Kubernetes\Contracts\ClientFactoryInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\ClusterCredentials;
use Teknoo\Kubernetes\Client;
use Teknoo\Kubernetes\RepositoryRegistry;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Contracts\Object\AccountComponentInterface;
use Teknoo\Space\Object\Config\Cluster;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements SluggableInterface<IdentifiedObjectInterface>
 */
#[ClassGroup('default', 'crud', 'api')]
class AccountCluster implements
    IdentifiedObjectInterface,
    TimestampableInterface,
    SluggableInterface,
    VisitableInterface,
    NormalizableInterface,
    AccountComponentInterface,
    \Stringable
{
    use ObjectTrait;
    use AutoTrait;
    use VisitableTrait {
        VisitableTrait::runVisit as realRunVisit;
    }

    public function __construct(
        private Account $account,
        #[Normalize(['default', 'crud', 'api'])]
        private string $name = '',
        #[Normalize(['default', 'crud', 'api'])]
        private string $slug = '',
        #[Normalize(['crud', 'api'])]
        private string $type = '',
        #[Normalize(['crud', 'api'])]
        private string $masterAddress = '',
        #[Normalize('crud')]
        private string $storageProvisioner = '',
        #[Normalize(['crud', 'api'])]
        private string $dashboardAddress = '',
        #[Normalize('crud')]
        private string $caCertificate = '',
        #[SensitiveParameter]
        #[Normalize('crud', loader: static function (): string {
            return '';
        })]
        private string $token = '',
        #[Normalize('crud')]
        private bool $supportRegistry = false,
        #[Normalize('crud')]
        private ?string $registryUrl = null,
        #[Normalize('crud')]
        private bool $useHnc = false,
    ) {
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): AccountCluster
    {
        $this->account = $account;
        return $this;
    }

    public function setCaCertificate(string $caCertificate): AccountCluster
    {
        $this->caCertificate = $caCertificate;

        return $this;
    }

    public function setDashboardAddress(string $dashboardAddress): AccountCluster
    {
        $this->dashboardAddress = $dashboardAddress;

        return $this;
    }

    public function setMasterAddress(string $masterAddress): AccountCluster
    {
        $this->masterAddress = $masterAddress;

        return $this;
    }

    public function setName(string $name): AccountCluster
    {
        $this->name = $name;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function setRegistryUrl(?string $registryUrl): AccountCluster
    {
        $this->registryUrl = $registryUrl;

        return $this;
    }

    public function setStorageProvisioner(string $storageProvisioner): AccountCluster
    {
        $this->storageProvisioner = $storageProvisioner;

        return $this;
    }

    public function setSupportRegistry(bool $supportRegistry): AccountCluster
    {
        $this->supportRegistry = $supportRegistry;

        return $this;
    }

    public function setToken(#[SensitiveParameter] ?string $token): AccountCluster
    {
        if (!empty($token)) {
            $this->token = $token;
        }

        return $this;
    }

    public function setType(string $type): AccountCluster
    {
        $this->type = $type;

        return $this;
    }

    public function setUseHnc(bool $useHnc): AccountCluster
    {
        $this->useHnc = $useHnc;

        return $this;
    }

    public function prepareSlugNear(
        LoaderInterface $loader,
        FindSlugService $findSlugService,
        string $slugField
    ): SluggableInterface {
        $slugValue = $this->slug;
        if (empty($slugValue)) {
            $slugValue = $this->name;
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

    public function convertToConfigCluster(
        ClientFactoryInterface $clientFactory,
        RepositoryRegistry $repositoryRegistry,
    ): Cluster {
        $caCertificate = base64_decode($this->caCertificate);

        $credentials = new ClusterCredentials(
            caCertificate: $caCertificate,
            token: $this->token,
        );

        return new Cluster(
            name: $this->name,
            sluggyName: $this->slug,
            type: $this->type,
            masterAddress: $this->masterAddress,
            storageProvisioner: $this->storageProvisioner,
            dashboardAddress: $this->dashboardAddress,
            kubernetesClient: fn (): Client => ($clientFactory)(
                $this->masterAddress,
                $credentials,
                $repositoryRegistry,
            ),
            token: $this->token,
            supportRegistry: $this->supportRegistry,
            useHnc: $this->useHnc,
            isExternal: true,
        );
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
