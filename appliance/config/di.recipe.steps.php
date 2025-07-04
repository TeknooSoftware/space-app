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
 * @link        https://teknoo.software/applications/space Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\App\Config;

use Endroid\QrCode\Writer\PngWriter;
use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Common\Service\FindSlugService;
use Teknoo\East\CommonBundle\Contracts\Recipe\Step\BuildQrCodeInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Foundation\Time\SleepServiceInterface;
use Teknoo\East\Paas\Infrastructures\Kubernetes\Contracts\ClientFactoryInterface;
use Teknoo\East\Paas\Loader\AccountLoader;
use Teknoo\East\Paas\Loader\ProjectLoader;
use Teknoo\Kubernetes\HttpClientDiscovery;
use Teknoo\Kubernetes\RepositoryRegistry;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\ClustersInfoInterface;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\DashboardFrameInterface;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\HealthInterface;
use Teknoo\Space\Contracts\Recipe\Step\Subscription\CreateAccountInterface;
use Teknoo\Space\Contracts\Recipe\Step\Subscription\CreateUserInterface;
use Teknoo\Space\Infrastructures\Endroid\QrCode\Recipe\Step\BuildQrCode;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateNamespace;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\PrepareAccountErrorHandler;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReinstallAccountErrorHandler;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReloadNamespace;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateDockerSecret;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateQuota;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateRole;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateRoleBinding;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateSecretServiceAccountToken;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\CreateServiceAccount;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\DeleteNamespaceFromResumes;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\PrepareInstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Misc\ClustersInfo;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Misc\DashboardFrame;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Misc\Health;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Registry\CreateRegistryDeployment;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Registry\CreateStorage;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Account\PrepareForm as PrepareAccountForm;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Client\SetRedirectClientAtEnd;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Subscription\CreateUser;
use Teknoo\Space\Loader\AccountClusterLoader;
use Teknoo\Space\Loader\AccountDataLoader;
use Teknoo\Space\Loader\AccountEnvironmentLoader;
use Teknoo\Space\Loader\AccountHistoryLoader;
use Teknoo\Space\Loader\AccountPersistedVariableLoader;
use Teknoo\Space\Loader\AccountRegistryLoader;
use Teknoo\Space\Loader\Meta\SpaceAccountLoader;
use Teknoo\Space\Loader\ProjectMetadataLoader;
use Teknoo\Space\Loader\ProjectPersistedVariableLoader;
use Teknoo\Space\Loader\UserDataLoader;
use Teknoo\Space\Recipe\Step\Account\CreateAccountHistory;
use Teknoo\Space\Recipe\Step\Account\InjectToView;
use Teknoo\Space\Recipe\Step\Account\LoadAccountFromRequest;
use Teknoo\Space\Recipe\Step\Account\LoadSpaceAccountFromAccount;
use Teknoo\Space\Recipe\Step\Account\LoadSubscriptionPlan;
use Teknoo\Space\Recipe\Step\Account\PrepareRedirection as AccountPrepareRedirection;
use Teknoo\Space\Recipe\Step\Account\SetAccountNamespace;
use Teknoo\Space\Recipe\Step\Account\SetQuota;
use Teknoo\Space\Recipe\Step\Account\SetSubscriptionPlan;
use Teknoo\Space\Recipe\Step\Account\UpdateAccountHistory;
use Teknoo\Space\Recipe\Step\AccountCluster\LoadAccountClusters;
use Teknoo\Space\Recipe\Step\AccountData\LoadData as LoadAccountData;
use Teknoo\Space\Recipe\Step\AccountEnvironment\CreateResumes;
use Teknoo\Space\Recipe\Step\AccountEnvironment\DeleteEnvFromResumes;
use Teknoo\Space\Recipe\Step\AccountEnvironment\ExtractResumes;
use Teknoo\Space\Recipe\Step\AccountEnvironment\FindEnvironmentInWallet;
use Teknoo\Space\Recipe\Step\AccountEnvironment\LoadEnvironments;
use Teknoo\Space\Recipe\Step\AccountEnvironment\PersistEnvironment;
use Teknoo\Space\Recipe\Step\AccountEnvironment\ReloadEnvironement;
use Teknoo\Space\Recipe\Step\AccountEnvironment\RemoveEnvironment;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;
use Teknoo\Space\Recipe\Step\AccountRegistry\LoadRegistryCredential;
use Teknoo\Space\Recipe\Step\AccountRegistry\PersistRegistryCredential;
use Teknoo\Space\Recipe\Step\AccountRegistry\RemoveRegistryCredential;
use Teknoo\Space\Recipe\Step\ClusterConfig\SelectClusterConfig;
use Teknoo\Space\Recipe\Step\Job\ExtractProject;
use Teknoo\Space\Recipe\Step\Job\IncludeExtraInWorkplan;
use Teknoo\Space\Recipe\Step\Job\JobSetDefaults;
use Teknoo\Space\Recipe\Step\Job\PrepareNewJobForm;
use Teknoo\Space\Recipe\Step\Misc\ClusterAndEnvSelection;
use Teknoo\Space\Recipe\Step\NewJob\NewJobSetDefaults;
use Teknoo\Space\Recipe\Step\PersistedVariable\LoadPersistedVariablesForJob;
use Teknoo\Space\Recipe\Step\Project\AddManagedEnvironmentToProject;
use Teknoo\Space\Recipe\Step\Project\CheckingAllowedCountOfProjects;
use Teknoo\Space\Recipe\Step\Project\LoadAccountFromProject;
use Teknoo\Space\Recipe\Step\Project\PrepareProject;
use Teknoo\Space\Recipe\Step\Project\UpdateProjectCredentialsFromAccount;
use Teknoo\Space\Recipe\Step\ProjectMetadata\InjectToViewMetadata;
use Teknoo\Space\Recipe\Step\ProjectMetadata\LoadProjectMetadata;
use Teknoo\Space\Recipe\Step\SpaceProject\PrepareRedirection as SpaceProjectPrepareRedirection;
use Teknoo\Space\Recipe\Step\SpaceProject\WorkplanInit;
use Teknoo\Space\Recipe\Step\Subscription\CreateAccount;
use Teknoo\Space\Recipe\Step\Subscription\InjectStatus;
use Teknoo\Space\Recipe\Step\UserData\LoadData as LoadUserData;
use Teknoo\Space\Writer\AccountEnvironmentWriter;
use Teknoo\Space\Writer\AccountHistoryWriter;
use Teknoo\Space\Writer\AccountRegistryWriter;
use Teknoo\Space\Writer\Meta\SpaceAccountWriter;

use function DI\create;
use function DI\get;

return [
    SelectClusterConfig::class => create(),

    SetAccountNamespace::class => create()
        ->constructor(
            get(FindSlugService::class),
            get(AccountLoader::class),
            get('teknoo.space.kubernetes.root_namespace'),
        ),

    CreateNamespace::class => static function (ContainerInterface $container): CreateNamespace {
        return new CreateNamespace(
            $container->get('teknoo.space.kubernetes.root_namespace'),
            $container->get('teknoo.space.kubernetes.registry_root_namespace'),
            $container->get(DatesService::class),
            !empty($container->get('teknoo.space.prefer-real-date')),
        );
    },

    DeleteNamespaceFromResumes::class => create()
        ->constructor(
            get('teknoo.space.clusters_catalog'),
        ),

    ReloadNamespace::class => create(),

    ReloadEnvironement::class => create(),

    CreateServiceAccount::class => static function (ContainerInterface $container): CreateServiceAccount {
        return new CreateServiceAccount(
            $container->get(DatesService::class),
            !empty($container->get('teknoo.space.prefer-real-date')),
        );
    },

    CreateQuota::class => static function (ContainerInterface $container): CreateQuota {
        return new CreateQuota(
            $container->get(DatesService::class),
            !empty($container->get('teknoo.space.prefer-real-date')),
        );
    },

    CreateRole::class => static function (ContainerInterface $container): CreateRole {
        return new CreateRole(
            $container->get(DatesService::class),
            !empty($container->get('teknoo.space.prefer-real-date')),
        );
    },

    CreateRoleBinding::class => static function (ContainerInterface $container): CreateRoleBinding {
        return new CreateRoleBinding(
            $container->get(DatesService::class),
            !empty($container->get('teknoo.space.prefer-real-date')),
        );
    },

    CreateSecretServiceAccountToken::class => static function (
        ContainerInterface $container,
    ): CreateSecretServiceAccountToken {
        return new CreateSecretServiceAccountToken(
            $container->get(DatesService::class),
            $container->get(SleepServiceInterface::class),
            (int) $container->get('teknoo.space.kubernetes.secret_account_token_waiting_time'),
            !empty($container->get('teknoo.space.prefer-real-date')),
        );
    },

    CreateStorage::class => static function (ContainerInterface $container): CreateStorage {
        return new CreateStorage(
            $container->get(DatesService::class),
            !empty($container->get('teknoo.space.prefer-real-date')),
        );
    },

    CreateRegistryDeployment::class => static function (ContainerInterface $container): CreateRegistryDeployment {
        return new CreateRegistryDeployment(
            registryImageName: $container->get('teknoo.space.kubernetes.oci_registry.image'),
            registryCpuRequests: $container->get('teknoo.space.kubernetes.oci_registry.requests.cpu'),
            registryMemoryRequests: $container->get('teknoo.space.kubernetes.oci_registry.requests.memory'),
            registryCpuLimits: $container->get('teknoo.space.kubernetes.oci_registry.limits.cpu'),
            registryMemoryLimits: $container->get('teknoo.space.kubernetes.oci_registry.limits.memory'),
            tlsSecretName: $container->get('teknoo.space.kubernetes.oci_registry.tls_secret_name'),
            registryUrl: $container->get('teknoo.space.kubernetes.oci_registry.url'),
            clusterIssuer: $container->get('teknoo.space.kubernetes.cluster_issuer'),
            datesService: $container->get(DatesService::class),
            preferRealDate: !empty($container->get('teknoo.space.prefer-real-date')),
            ingressClass: $container->get('teknoo.east.paas.kubernetes.ingress.default_ingress_class'),
        );
    },

    CreateDockerSecret::class => static function (ContainerInterface $container): CreateDockerSecret {
        return new CreateDockerSecret(
            datesService: $container->get(DatesService::class),
            preferRealDate: !empty($container->get('teknoo.space.prefer-real-date')),
            spaceRegistryUrl: $container->get('teknoo.space.kubernetes.oci_space_global_registry.url'),
            spaceRegistryUsername: $container->get('teknoo.space.kubernetes.oci_space_global_registry.username'),
            spaceRegistryPwd: $container->get('teknoo.space.kubernetes.oci_space_global_registry.pwd'),
        );
    },

    PersistEnvironment::class => static function (ContainerInterface $container): PersistEnvironment {
        return new PersistEnvironment(
            writer: $container->get(AccountEnvironmentWriter::class),
            datesService: $container->get(DatesService::class),
            preferRealDate: !empty($container->get('teknoo.space.prefer-real-date')),
        );
    },

    PersistRegistryCredential::class => static function (ContainerInterface $container): PersistRegistryCredential {
        return new PersistRegistryCredential(
            writer: $container->get(AccountRegistryWriter::class),
            datesService: $container->get(DatesService::class),
            preferRealDate: !empty($container->get('teknoo.space.prefer-real-date')),
        );
    },

    RemoveEnvironment::class => create()
        ->constructor(
            get(AccountEnvironmentWriter::class),
        ),

    RemoveRegistryCredential::class => create()
        ->constructor(
            get(AccountRegistryWriter::class),
        ),

    PrepareProject::class => create(),

    SetRedirectClientAtEnd::class => create()
        ->constructor(
            get(ResponseFactoryInterface::class),
            get('router'),
        ),

    PrepareAccountErrorHandler::class => static function (ContainerInterface $container): PrepareAccountErrorHandler {
        return new PrepareAccountErrorHandler(
            $container->get(DatesService::class),
            $container->get(AccountHistoryWriter::class),
            !empty($container->get('teknoo.space.prefer-real-date')),
        );
    },

    ReinstallAccountErrorHandler::class => static function (
        ContainerInterface $container
    ): ReinstallAccountErrorHandler {
        return new ReinstallAccountErrorHandler(
            $container->get(DatesService::class),
            $container->get(AccountHistoryWriter::class),
            !empty($container->get('teknoo.space.prefer-real-date')),
        );
    },

    CreateAccountHistory::class => static function (ContainerInterface $container): CreateAccountHistory {
        return new CreateAccountHistory(
            $container->get(AccountHistoryWriter::class),
            $container->get(DatesService::class),
            !empty($container->get('teknoo.space.prefer-real-date')),
        );
    },

    UpdateAccountHistory::class => create()
        ->constructor(
            get(AccountHistoryWriter::class),
        ),

    LoadEnvironments::class => create()
        ->constructor(get(AccountEnvironmentLoader::class)),

    CreateResumes::class => create(),

    ExtractResumes::class => create(),

    FindEnvironmentInWallet::class => create(),

    DeleteEnvFromResumes::class => create()
        ->constructor(get(AccountEnvironmentWriter::class)),

    LoadRegistryCredential::class => create()
        ->constructor(get(AccountRegistryLoader::class)),

    LoadAccountFromRequest::class => create()
        ->constructor(get(SpaceAccountLoader::class)),

    LoadAccountData::class => create()
        ->constructor(get(AccountDataLoader::class)),

    LoadAccountClusters::class => create()
        ->constructor(
            get(AccountClusterLoader::class),
            get(ClientFactoryInterface::class),
            get(RepositoryRegistry::class),
        ),

    InjectToView::class => create(),

    LoadHistory::class => create()
        ->constructor(
            get(AccountHistoryLoader::class),
            get(AccountHistoryWriter::class),
        ),

    LoadPersistedVariablesForJob::class => create()
        ->constructor(
            get(AccountPersistedVariableLoader::class),
            get(ProjectPersistedVariableLoader::class)
        ),

    PrepareNewJobForm::class => create(),

    AccountPrepareRedirection::class => create(),

    WorkplanInit::class => create(),

    PrepareAccountForm::class => create()
        ->constructor(
            get('teknoo.space.subscription_plan_catalog')
        ),

    SpaceProjectPrepareRedirection::class => create(),

    ExtractProject::class => create(),

    InjectToViewMetadata::class => create(),

    LoadProjectMetadata::class => create()
        ->constructor(get(ProjectMetadataLoader::class)),

    CreateAccountInterface::class => get(CreateAccount::class),

    CreateAccount::class => create()
        ->constructor(
            get(SpaceAccountWriter::class),
        ),

    LoadSpaceAccountFromAccount::class => create()
        ->constructor(
            get(SpaceAccountLoader::class)
        ),

    LoadSubscriptionPlan::class => create()
        ->constructor(
            get('teknoo.space.subscription_plan_catalog'),
        ),

    SetSubscriptionPlan::class => create()
        ->constructor(
            get('teknoo.space.subscription_plan_catalog'),
        ),

    SetQuota::class => create(),

    PrepareInstall::class => create(),

    CreateUserInterface::class => get(CreateUser::class),

    LoadUserData::class => create()
        ->constructor(get(UserDataLoader::class)),

    LoadAccountFromProject::class => create(),

    AddManagedEnvironmentToProject::class => create()
        ->constructor(
            get('teknoo.space.clusters_catalog'),
        ),

    UpdateProjectCredentialsFromAccount::class => create()
        ->constructor(
            get('teknoo.space.clusters_catalog'),
        ),

    NewJobSetDefaults::class => create()
        ->constructor(
            get('teknoo.space.clusters_catalog'),
        ),

    JobSetDefaults::class => create(),

    IncludeExtraInWorkplan::class => create(),

    HealthInterface::class => get(Health::class),
    Health::class => create()
        ->constructor(
            get('teknoo.space.clusters_catalog'),
        ),

    ClustersInfoInterface::class => get(ClustersInfo::class),
    ClustersInfo::class => create()
        ->constructor(
            get('teknoo.space.clusters_catalog'),
        ),

    ClusterAndEnvSelection::class => create()
        ->constructor(
            get('teknoo.space.clusters_catalog'),
        ),

    DashboardFrameInterface::class => get(DashboardFrame::class),
    DashboardFrame::class => static function (ContainerInterface $container): DashboardFrame {
        if ($container->has(ClientInterface::class)) {
            $httpClient = $container->get(ClientInterface::class);
        } else {
            $httpClient = HttpClientDiscovery::find(
                verify: (bool) $container->get('teknoo.east.paas.kubernetes.ssl.verify'),
            );
        }

        if ($container->has(RequestFactoryInterface::class)) {
            $httpRequestFactory = $container->get(RequestFactoryInterface::class);
        } else {
            $httpRequestFactory = Psr17FactoryDiscovery::findRequestFactory();
        }

        if ($container->has(StreamFactoryInterface::class)) {
            $httpStreamFactory = $container->get(StreamFactoryInterface::class);
        } else {
            $httpStreamFactory = Psr17FactoryDiscovery::findStreamFactory();
        }

        $httpMethodsClient = new HttpMethodsClient(
            $httpClient,
            $httpRequestFactory,
            $httpStreamFactory,
        );

        return new DashboardFrame(
            httpMethodsClient: $httpMethodsClient,
            responseFactory: Psr17FactoryDiscovery::findResponseFactory(),
        );
    },

    PngWriter::class => create(),

    BuildQrCodeInterface::class => get(BuildQrCode::class),
    BuildQrCode::class => create()
        ->constructor(
            get(PngWriter::class),
            get(StreamFactoryInterface::class),
        ),

    CheckingAllowedCountOfProjects::class => create()
        ->constructor(
            get(ProjectLoader::class),
        ),

    InjectStatus::class => create()
        ->constructor(
            get(ProjectLoader::class),
        ),
];
