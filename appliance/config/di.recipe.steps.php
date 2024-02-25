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

namespace Teknoo\Space\App\Config;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Builder\BuilderInterface;
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
use Teknoo\East\Paas\Loader\AccountLoader;
use Teknoo\East\Paas\Writer\AccountWriter;
use Teknoo\Kubernetes\HttpClientDiscovery;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\DashboardFrameInterface;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\DashboardInfoInterface;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\HealthInterface;
use Teknoo\Space\Contracts\Recipe\Step\Subscription\CreateAccountInterface;
use Teknoo\Space\Contracts\Recipe\Step\Subscription\CreateUserInterface;
use Teknoo\Space\Infrastructures\Endroid\QrCode\Recipe\Step\BuildQrCode;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateNamespace;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateRegistryAccount;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateRole;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateRoleBinding;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateSecretServiceAccountToken;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateServiceAccount;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateStorage;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\PrepareAccountErrorHandler;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReinstallAccountErrorHandler;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReloadNamespace;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Misc\DashboardFrame;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Misc\DashboardInfo;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Misc\Health;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Client\SetRedirectClientAtEnd;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Subscription\CreateUser;
use Teknoo\Space\Loader\AccountCredentialLoader;
use Teknoo\Space\Loader\AccountDataLoader;
use Teknoo\Space\Loader\AccountHistoryLoader;
use Teknoo\Space\Loader\AccountPersistedVariableLoader;
use Teknoo\Space\Loader\PersistedVariableLoader;
use Teknoo\Space\Loader\ProjectMetadataLoader;
use Teknoo\Space\Loader\UserDataLoader;
use Teknoo\Space\Recipe\Step\Account\CreateAccountHistory;
use Teknoo\Space\Recipe\Step\Account\PrepareRedirection as AccountPrepareRedirection;
use Teknoo\Space\Recipe\Step\Account\SetAccountNamespace;
use Teknoo\Space\Recipe\Step\Account\UpdateAccountHistory;
use Teknoo\Space\Recipe\Step\AccountCredential\LoadCredentials;
use Teknoo\Space\Recipe\Step\AccountCredential\PersistCredentials;
use Teknoo\Space\Recipe\Step\AccountCredential\RemoveCredentials;
use Teknoo\Space\Recipe\Step\AccountCredential\UpdateCredentials;
use Teknoo\Space\Recipe\Step\AccountData\LoadData as LoadAccountData;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;
use Teknoo\Space\Recipe\Step\Job\ExtractProject;
use Teknoo\Space\Recipe\Step\Job\IncludeExtraInWorkplan;
use Teknoo\Space\Recipe\Step\Job\JobSetDefaults;
use Teknoo\Space\Recipe\Step\Job\PrepareNewJobForm;
use Teknoo\Space\Recipe\Step\PersistedVariable\LoadPersistedVariablesForJob;
use Teknoo\Space\Recipe\Step\Project\LoadAccountFromProject;
use Teknoo\Space\Recipe\Step\Project\PrepareProject;
use Teknoo\Space\Recipe\Step\Project\UpdateProjectCredentialsFromAccount;
use Teknoo\Space\Recipe\Step\ProjectMetadata\InjectToViewMetadata;
use Teknoo\Space\Recipe\Step\ProjectMetadata\LoadProjectMetadata;
use Teknoo\Space\Recipe\Step\SpaceProject\PrepareRedirection as SpaceProjectPrepareRedirection;
use Teknoo\Space\Recipe\Step\SpaceProject\WorkplanInit;
use Teknoo\Space\Recipe\Step\Subscription\CreateAccount;
use Teknoo\Space\Recipe\Step\UserData\LoadData as LoadUserData;
use Teknoo\Space\Writer\AccountCredentialWriter;
use Teknoo\Space\Writer\AccountHistoryWriter;
use Teknoo\Space\Writer\Meta\SpaceAccountWriter;
use Teknoo\Space\Writer\Meta\SpaceUserWriter;

use function DI\create;
use function DI\get;

return [
    SetAccountNamespace::class => create()
        ->constructor(
            get(FindSlugService::class),
            get(AccountLoader::class),
        ),

    CreateNamespace::class => static function (ContainerInterface $container): CreateNamespace {
        return new CreateNamespace(
            $container->get('teknoo.space.kubernetes.root_namespace'),
            $container->get(DatesService::class),
            !empty($container->get('teknoo.space.prefer-real-date')),
            $container->get(AccountWriter::class),
        );
    },

    ReloadNamespace::class => create()
        ->constructor(
            get('teknoo.space.clusters_catalog'),
        ),

    CreateServiceAccount::class => static function (ContainerInterface $container): CreateServiceAccount {
        return new CreateServiceAccount(
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
            $container->get('teknoo.east.paas.default_storage_provider'),
            !empty($container->get('teknoo.space.prefer-real-date')),
        );
    },

    CreateRegistryAccount::class => static function (ContainerInterface $container): CreateRegistryAccount {
        return new CreateRegistryAccount(
            registryImageName: $container->get('teknoo.space.kubernetes.oci_registry.image'),
            tlsSecretName: $container->get('teknoo.space.kubernetes.oci_registry.tls_secret_name'),
            registryUrl: $container->get('teknoo.space.kubernetes.oci_registry.url'),
            clusterIssuer: $container->get('teknoo.space.kubernetes.cluster_issuer'),
            datesService: $container->get(DatesService::class),
            prefereRealDate: !empty($container->get('teknoo.space.prefer-real-date')),
            ingressClass: $container->get('teknoo.east.paas.kubernetes.ingress.default_ingress_class'),
            spaceRegistryUrl: $container->get('teknoo.space.kubernetes.oci_space_global_registry.url'),
            spaceRegistryUsername: $container->get('teknoo.space.kubernetes.oci_space_global_registry.username'),
            spaceRegistryPwd: $container->get('teknoo.space.kubernetes.oci_space_global_registry.pwd'),
        );
    },

    PersistCredentials::class => static function (ContainerInterface $container): PersistCredentials {
        return new PersistCredentials(
            writer: $container->get(AccountCredentialWriter::class),
            datesService: $container->get(DatesService::class),
            prefereRealDate: !empty($container->get('teknoo.space.prefer-real-date')),
        );
    },

    UpdateCredentials::class => static function (ContainerInterface $container): UpdateCredentials {
        return new UpdateCredentials(
            $container->get(AccountCredentialWriter::class),
            $container->get(DatesService::class),
            !empty($container->get('teknoo.space.prefer-real-date')),
        );
    },

    RemoveCredentials::class => create()
        ->constructor(
            get(AccountCredentialWriter::class),
        ),

    PrepareProject::class => create()
        ->constructor(
            get('teknoo.space.clusters_catalog'),
        ),

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

    LoadCredentials::class => create()
        ->constructor(get(AccountCredentialLoader::class)),

    LoadAccountData::class => create()
        ->constructor(get(AccountDataLoader::class)),

    LoadHistory::class => create()
        ->constructor(
            get(AccountHistoryLoader::class),
            get(AccountHistoryWriter::class),
        ),

    LoadPersistedVariablesForJob::class => create()
        ->constructor(
            get(AccountPersistedVariableLoader::class),
            get(PersistedVariableLoader::class)
        ),

    PrepareNewJobForm::class => create(),

    AccountPrepareRedirection::class => create(),

    WorkplanInit::class => create(),

    SpaceProjectPrepareRedirection::class => create(),

    ExtractProject::class => create(),

    InjectToViewMetadata::class => create(),

    LoadProjectMetadata::class => create()
        ->constructor(get(ProjectMetadataLoader::class)),

    CreateAccountInterface::class => get(CreateAccount::class),

    CreateAccount::class => create()
        ->constructor(get(SpaceAccountWriter::class)),

    CreateUserInterface::class => get(CreateUser::class),

    CreateUser::class => create()
        ->constructor(get(SpaceUserWriter::class)),

    LoadUserData::class => create()
        ->constructor(get(UserDataLoader::class)),

    LoadAccountFromProject::class => create(),

    UpdateProjectCredentialsFromAccount::class => create()
        ->constructor(
            get('teknoo.space.clusters_catalog'),
        ),

    JobSetDefaults::class => create()
        ->constructor(
            get('teknoo.space.clusters_catalog'),
        ),

    IncludeExtraInWorkplan::class => create(),

    HealthInterface::class => get(Health::class),
    Health::class => create()
        ->constructor(
            get('teknoo.space.clusters_catalog'),
        ),

    DashboardInfoInterface::class => get(DashboardInfo::class),
    DashboardInfo::class => create()
        ->constructor(),

    DashboardFrameInterface::class => get(DashboardFrame::class),
    DashboardFrame::class => function (ContainerInterface $container): DashboardFrame {
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
            catalog: $container->get('teknoo.space.clusters_catalog'),
            httpMethodsClient: $httpMethodsClient,
            responseFactory: Psr17FactoryDiscovery::findResponseFactory(),
        );
    },

    PngWriter::class => create(),
    BuilderInterface::class => static function (): BuilderInterface {
        return Builder::create();
    },

    BuildQrCodeInterface::class => get(BuildQrCode::class),
    BuildQrCode::class => create()
        ->constructor(
            get(BuilderInterface::class),
            get(PngWriter::class),
            get(StreamFactoryInterface::class),
        ),
];
