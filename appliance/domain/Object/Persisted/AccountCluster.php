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
use Teknoo\East\Foundation\Normalizer\EastNormalizerInterface;
use Teknoo\East\Foundation\Normalizer\Object\GroupsTrait;
use Teknoo\East\Foundation\Normalizer\Object\NormalizableInterface;
use Teknoo\East\Paas\Infrastructures\Kubernetes\Contracts\ClientFactoryInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\ClusterCredentials;
use Teknoo\East\Paas\Object\Traits\ExportConfigurationsTrait;
use Teknoo\Kubernetes\RepositoryRegistry;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Contracts\Object\AccountComponentInterface;
use Teknoo\Space\Object\Config\Cluster;

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
    SluggableInterface,
    VisitableInterface,
    NormalizableInterface,
    AccountComponentInterface
{
    use ObjectTrait;
    use GroupsTrait;
    use ExportConfigurationsTrait;
    use VisitableTrait {
        VisitableTrait::runVisit as realRunVisit;
    }

    /**
     * @var array<string, string[]>
     */
    private static array $exportConfigurations = [
        '@class' => ['default', 'crud', 'api'],
        'name' => ['default', 'crud', 'api'],
        'slug' => ['default', 'crud', 'api'],
        'type' => ['crud', 'api'],
        'masterAddress' => ['crud', 'api'],
        'storageProvisioner' => ['crud'],
        'dashboardAddress' => ['crud', 'api'],
        'caCertificate' => ['crud'],
        'token' => ['crud'],
        'supportRegistry' => ['crud'],
        'registryUrl' => ['crud'],
        'useHnc' => ['crud'],
    ];

    public function __construct(
        private Account $account,
        private string $name = '',
        private string $slug = '',
        private string $type = '',
        private string $masterAddress = '',
        private string $storageProvisioner = '',
        private string $dashboardAddress = '',
        private string $caCertificate = '',
        #[SensitiveParameter]
        private string $token = '',
        private bool $supportRegistry = false,
        private ?string $registryUrl = null,
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
            kubernetesClient: fn() => ($clientFactory)(
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

    public function exportToMeData(EastNormalizerInterface $normalizer, array $context = []): NormalizableInterface
    {
        $data = [
            '@class' => self::class,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'masterAddress' => $this->masterAddress,
            'storageProvisioner' => $this->storageProvisioner,
            'dashboardAddress' => $this->dashboardAddress,
            'caCertificate' => $this->caCertificate,
            'token' => '',
            'supportRegistry' => $this->supportRegistry,
            'registryUrl' => $this->registryUrl,
            'useHnc' => $this->useHnc,
        ];

        $this->setGroupsConfiguration(self::$exportConfigurations);

        $normalizer->injectData(
            $this->filterExport(
                data: $data,
                groups: (array) ($context['groups'] ?? ['default']),
            )
        );

        return $this;
    }

    public function verifyAccessToUser(User $user, PromiseInterface $promise): AccountComponentInterface
    {
        $this->account->verifyAccessToUser($user, $promise);

        return $this;
    }
}
