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

namespace Teknoo\Space\App\Config;

use Psr\Container\ContainerInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ListObjectsAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\SearchFormLoaderInterface;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\DeleteObject;
use Teknoo\East\Common\Recipe\Step\EndLooping;
use Teknoo\East\Common\Recipe\Step\ExtractOrder;
use Teknoo\East\Common\Recipe\Step\ExtractPage;
use Teknoo\East\Common\Recipe\Step\JumpIf;
use Teknoo\East\Common\Recipe\Step\JumpIfNot;
use Teknoo\East\Common\Recipe\Step\LoadListObjects;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\RenderList;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\East\Common\Recipe\Step\StartLoopingOn;
use Teknoo\East\Common\Recipe\Step\Stop;
use Teknoo\East\Paas\Contracts\Recipe\Plan\EditAccountEndPointInterface;
use Teknoo\East\Paas\Contracts\Recipe\Plan\EditProjectEndPointInterface;
use Teknoo\East\Paas\Recipe\Plan\AbstractEditObjectEndPoint;
use Teknoo\East\Paas\Recipe\Plan\NewAccountEndPoint;
use Teknoo\East\Paas\Recipe\Plan\NewJob;
use Teknoo\East\Paas\Recipe\Plan\NewProjectEndPoint;
use Teknoo\East\Paas\Recipe\Plan\RunJob;
use Teknoo\Recipe\Bowl\RecipeBowl;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\Plan\Step;
use Teknoo\Recipe\PlanInterface;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;
use Teknoo\Recipe\Value;
use Teknoo\Space\Contracts\Recipe\Step\Contact\SendEmailInterface;
use Teknoo\Space\Contracts\Recipe\Step\Job\CallNewJobInterface;
use Teknoo\Space\Contracts\Recipe\Step\Job\FetchJobIdFromPendingInterface;
use Teknoo\Space\Contracts\Recipe\Step\Job\NewJobNotifierInterface;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\ClustersInfoInterface;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\DashboardFrameInterface;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\HealthInterface;
use Teknoo\Space\Contracts\Recipe\Step\Subscription\CreateAccountInterface;
use Teknoo\Space\Contracts\Recipe\Step\Subscription\CreateUserInterface;
use Teknoo\Space\Contracts\Recipe\Step\Subscription\LoginUserInterface;
use Teknoo\Space\Contracts\Recipe\Step\User\JwtCreateTokenInterface;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Plan\AccountEnvironmentInstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Plan\AccountEnvironmentReinstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Plan\AccountRefreshQuota;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Plan\AccountRegistryInstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Plan\AccountRegistryReinstall;
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
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Registry\CreateRegistryDeployment;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Registry\CreateStorage;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Account\PrepareForm as PrepareAccountForm;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Client\SetRedirectClientAtEnd;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Job\JobErrorNotifier;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Job\JobUpdaterNotifier;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Job\PersistJobVar;
use Teknoo\Space\Object\DTO\AccountEnvironmentResume;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\DTO\SpaceUser;
use Teknoo\Space\Recipe\Plan\AccountClusterDelete;
use Teknoo\Space\Recipe\Plan\AccountClusterEdit;
use Teknoo\Space\Recipe\Plan\AccountClusterList;
use Teknoo\Space\Recipe\Plan\AccountClusterNew;
use Teknoo\Space\Recipe\Plan\AccountEditSettings;
use Teknoo\Space\Recipe\Plan\AccountStatus;
use Teknoo\Space\Recipe\Plan\AdminAccountStatus;
use Teknoo\Space\Recipe\Plan\Contact;
use Teknoo\Space\Recipe\Plan\Dashboard;
use Teknoo\Space\Recipe\Plan\DashboardFrame;
use Teknoo\Space\Recipe\Plan\FormWithoutObject;
use Teknoo\Space\Recipe\Plan\JobGet;
use Teknoo\Space\Recipe\Plan\JobList;
use Teknoo\Space\Recipe\Plan\JobPending;
use Teknoo\Space\Recipe\Plan\JobRestart;
use Teknoo\Space\Recipe\Plan\JobStart;
use Teknoo\Space\Recipe\Plan\ProjectList;
use Teknoo\Space\Recipe\Plan\ProjectNew;
use Teknoo\Space\Recipe\Plan\RefreshProjectCredentials;
use Teknoo\Space\Recipe\Plan\Subscription;
use Teknoo\Space\Recipe\Plan\UserCreateJwtToken;
use Teknoo\Space\Recipe\Plan\UserCreateFromFormJwtToken;
use Teknoo\Space\Recipe\Plan\UserDeleteApiToken;
use Teknoo\Space\Recipe\Plan\UserManageApiTokens;
use Teknoo\Space\Recipe\Plan\UserMySettings;
use Teknoo\Space\Recipe\Step\Account\CreateAccountHistory;
use Teknoo\Space\Recipe\Step\Account\ExtractFromAccountDTO;
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
use Teknoo\Space\Recipe\Step\AccountEnvironment\CheckingAllowedCountOfEnvs;
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
use Teknoo\Space\Recipe\Step\ApiKey\RemoveKey;
use Teknoo\Space\Recipe\Step\ClusterConfig\SelectClusterConfig;
use Teknoo\Space\Recipe\Step\Job\ExtractProject;
use Teknoo\Space\Recipe\Step\Job\IncludeExtraInWorkplan;
use Teknoo\Space\Recipe\Step\Job\JobSetDefaults;
use Teknoo\Space\Recipe\Step\Job\PrepareCriteria as JobPrepareCriteria;
use Teknoo\Space\Recipe\Step\Job\PrepareNewJobForm;
use Teknoo\Space\Recipe\Step\Misc\ClusterAndEnvSelection;
use Teknoo\Space\Recipe\Step\Misc\PrepareCriteria as ProjectPrepareCriteria;
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
use Teknoo\Space\Recipe\Step\Subscription\InjectStatus;

use function DI\create;
use function DI\decorate;
use function DI\get as diGet;
use function DI\value;
use function is_a;

return [
    Subscription::class => static function (ContainerInterface $container): Subscription {
        $plan = new Subscription(
            $container->get(OriginalRecipeInterface::class),
            $container->get(CreateObject::class),
            $container->get(FormHandlingInterface::class),
            $container->get(FormProcessingInterface::class),
            $container->get(CreateUserInterface::class),
            $container->get(CreateAccountInterface::class),
            $container->get(LoginUserInterface::class),
            $container->get(RenderFormInterface::class),
            $container->get(RenderError::class),
            $container->get('teknoo.east.common.get_default_error_template'),
        );

        $steps = $container->get('teknoo.space.account.endpoint.new.additional_steps');

        foreach ($steps as $class => $position) {
            if (is_a($class, PlanInterface::class, true)) {
                $plan->add(
                    new RecipeBowl(
                        recipe: $container->get($class),
                        repeat: 0,
                    ),
                    $position
                );
            } else {
                $plan->add($container->get($class), $position);
            }
        }

        return $plan;
    },

    FormWithoutObject::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(CreateObject::class),
            diGet(FormHandlingInterface::class),
            diGet(RenderFormInterface::class),
            diGet(RenderError::class),
            diGet('teknoo.east.common.get_default_error_template'),
        ),

    AccountEnvironmentInstall::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(LoadAccountClusters::class),
            diGet(CreateNamespace::class),
            diGet(SelectClusterConfig::class),
            diGet(CreateServiceAccount::class),
            diGet(CreateQuota::class),
            diGet(CreateRole::class),
            diGet(CreateRoleBinding::class),
            diGet(CreateDockerSecret::class),
            diGet(CreateSecretServiceAccountToken::class),
            diGet(PersistEnvironment::class),
            diGet(PrepareAccountErrorHandler::class),
            diGet(ObjectAccessControlInterface::class),
        ),

    AccountEnvironmentReinstall::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(LoadObject::class),
            diGet(AccountPrepareRedirection::class),
            diGet(SetRedirectClientAtEnd::class),
            diGet(LoadHistory::class),
            diGet(LoadEnvironments::class),
            diGet(LoadRegistryCredential::class),
            diGet(ReloadNamespace::class),
            diGet(FindEnvironmentInWallet::class),
            diGet(RemoveEnvironment::class),
            diGet(AccountEnvironmentInstall::class),
            diGet(UpdateAccountHistory::class),
            diGet(JumpIf::class),
            diGet(Render::class),
            diGet(ReinstallAccountErrorHandler::class),
            diGet(ObjectAccessControlInterface::class),
        ),

    AccountRegistryInstall::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(LoadAccountClusters::class),
            diGet(CreateNamespace::class),
            diGet(CreateStorage::class),
            diGet(CreateRegistryDeployment::class),
            diGet(PersistRegistryCredential::class),
            diGet(PrepareAccountErrorHandler::class),
            diGet(ObjectAccessControlInterface::class),
            diGet('teknoo.east.paas.default_storage_size'),
        ),

    AccountRegistryReinstall::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(LoadObject::class),
            diGet(AccountPrepareRedirection::class),
            diGet(SetRedirectClientAtEnd::class),
            diGet(LoadHistory::class),
            diGet(LoadRegistryCredential::class),
            diGet(ReloadNamespace::class),
            diGet(RemoveRegistryCredential::class),
            diGet(AccountRegistryInstall::class),
            diGet(UpdateAccountHistory::class),
            diGet(JumpIf::class),
            diGet(Render::class),
            diGet(ReinstallAccountErrorHandler::class),
            diGet(ObjectAccessControlInterface::class),
            diGet('teknoo.east.paas.default_storage_size'),
        ),

    AccountRefreshQuota::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(LoadObject::class),
            diGet(AccountPrepareRedirection::class),
            diGet(SetRedirectClientAtEnd::class),
            diGet(LoadHistory::class),
            diGet(LoadEnvironments::class),
            diGet(LoadAccountClusters::class),
            diGet(ReloadNamespace::class),
            diGet(ReloadEnvironement::class),
            diGet(SelectClusterConfig::class),
            diGet(CreateQuota::class),
            diGet(UpdateAccountHistory::class),
            diGet(JumpIf::class),
            diGet(Render::class),
            diGet(ReinstallAccountErrorHandler::class),
            diGet(ObjectAccessControlInterface::class),
        ),

    'teknoo.space.account.endpoint.new.additional_steps' => [
        //After ObjectAccessControlInterface
        ExtractFromAccountDTO::class => 54,
        SetAccountNamespace::class => 55,
        SetSubscriptionPlan::class => 55,
        SetQuota::class => 55,

        //After SaveObject
        CreateAccountHistory::class => 61,
        AccountRegistryInstall::class => 62,
        UpdateAccountHistory::class => 69,

        //After RedirectClientInterface
    ],

    NewAccountEndPoint::class => decorate(
        static function (NewAccountEndPoint $previous, ContainerInterface $container): NewAccountEndPoint {
            $steps = $container->get('teknoo.space.account.endpoint.new.additional_steps');

            foreach ($steps as $class => $position) {
                if (is_a($class, PlanInterface::class, true)) {
                    $previous->add(
                        new RecipeBowl(
                            recipe: $container->get($class),
                            repeat: 0,
                        ),
                        $position
                    );
                } else {
                    $previous->add($container->get($class), $position);
                }
            }

            return $previous;
        },
    ),

    'teknoo.space.project.endpoint.new.additional_steps' => [
        //Before CreateObject
        LoadSpaceAccountFromAccount::class => 6,
        LoadRegistryCredential::class => 6,
        LoadEnvironments::class => 6,
        LoadAccountClusters::class => 6,
        CreateResumes::class => 7,

        //After CreateObject
        WorkplanInit::class => 11,
        PrepareProject::class => 15,

        //After ObjectAccessControlInterface
        LoadSubscriptionPlan::class => 51,
        CheckingAllowedCountOfProjects::class => 52,
        AddManagedEnvironmentToProject::class => 58,
        UpdateProjectCredentialsFromAccount::class => 59,

        //After SaveObject
        SpaceProjectPrepareRedirection::class => 69,
    ],

    NewProjectEndPoint::class => decorate(
        static function (
            NewProjectEndPoint $previous,
            ContainerInterface $container,
        ): NewProjectEndPoint {
            $steps = $container->get('teknoo.space.project.endpoint.new.additional_steps');

            foreach ($steps as $class => $position) {
                $previous->add($container->get($class), $position);
            }

            return $previous;
        },
    ),

    'teknoo.space.account.additional_steps.common' => static function (): callable {
        return static function (
            EditablePlanInterface $steps,
            ContainerInterface $container,
        ): EditablePlanInterface {
            //After LoadObject
            $steps->add($container->get(ExtractFromAccountDTO::class), 11);
            $steps->add($container->get(LoadAccountClusters::class), 12);
            $steps->add($container->get(LoadEnvironments::class), 12);
            $steps->add($container->get(CreateResumes::class), 13);
            $steps->add($container->get(LoadSubscriptionPlan::class), 13);
            $steps->add($container->get(InjectStatus::class), 14);

            //After ObjectAccessControlInterface
            $steps->add($container->get(LoadHistory::class), 25);

            //Before FormHandlingInterface
            $steps->add($container->get(PrepareAccountForm::class), 29);

            //After FormProcessingInterface
            $steps->add($container->get(SetSubscriptionPlan::class), 57);
            $steps->add($container->get(CheckingAllowedCountOfEnvs::class), 58);
            $steps->add($container->get(CreateAccountHistory::class), 58);

            //After SaveObject
            $steps->add($container->get(DeleteNamespaceFromResumes::class), 61);
            $steps->add($container->get(DeleteEnvFromResumes::class), 61);
            $steps->add($container->get(LoadRegistryCredential::class), 61);

            $steps->add(
                action: $container->get(ExtractResumes::class),
                position: 62,
            );

            $steps->add(
                action: new Step(
                    step: new StartLoopingOn(),
                    with: [
                        'collection' => 'accountEnvsResumes',
                    ],
                ),
                position: 63,
            );

            $steps->add(
                action: new Step(
                    step: $container->get(JumpIf::class),
                    with: [
                        'testValue' => AccountEnvironmentResume::class,
                        'expectedJumpValue' => new Value(
                            static fn (AccountEnvironmentResume $resume) => !empty($resume->accountEnvironmentId),
                        ),
                        'nextStep' => new Value(EndLooping::class),
                    ],
                ),
                position: 64,
            );

            $steps->add(
                action: $container->get(PrepareInstall::class),
                position: 65,
            );

            $steps->add(
                action: new RecipeBowl(
                    recipe: $container->get(AccountEnvironmentInstall::class),
                    repeat: 0,
                ),
                position: 66,
            );

            $steps->add(
                action: new EndLooping(),
                position: 67,
            );

            $steps->add($container->get(UpdateAccountHistory::class), 68);
            //After FormHandlingInterface::class . ':refresh'

            return $steps;
        };
    },

    EditAccountEndPointInterface::class => decorate(
        callable: static function (
            EditAccountEndPointInterface $previous,
            ContainerInterface $container
        ): EditAccountEndPointInterface {
            //After FormProcessingInterface
            $previous->add($container->get(SetAccountNamespace::class), 58);
            $previous->add($container->get(SetQuota::class), 58);

            $addStepsToManageEnv = $container->get('teknoo.space.account.additional_steps.common');
            return $addStepsToManageEnv($previous, $container);
        }
    ),

    EditProjectEndPointInterface::class => decorate(
        static function (
            EditProjectEndPointInterface $previous,
            ContainerInterface $container
        ): EditProjectEndPointInterface {
            //After LoadObject
            $previous->add($container->get(LoadAccountFromProject::class), 11);
            $previous->add($container->get(WorkplanInit::class), 11);
            $previous->add($container->get(LoadEnvironments::class), 12);
            $previous->add($container->get(LoadAccountClusters::class), 12);
            $previous->add($container->get(CreateResumes::class), 13);
            //After FormProcessingInterface
            $previous->add($container->get(AddManagedEnvironmentToProject::class), 58);

            return $previous;
        }
    ),

    RunJob::class => decorate(
        static function (RunJob $previous, ContainerInterface $container): RunJob {
            $previous->add(
                action: $container->get(IncludeExtraInWorkplan::class),
                position: 21
            );

            return $previous;
        }
    ),

    NewJob::class => decorate(
        static function (NewJob $previous, ContainerInterface $container): NewJob {
            //After PrepareJob
            $previous->add(
                action: $container->get(LoadAccountFromProject::class),
                position: 51,
            );
            $previous->add(
                action: $container->get(LoadEnvironments::class),
                position: 52,
            );
            $previous->add(
                action: $container->get(LoadRegistryCredential::class),
                position: 52,
            );
            $previous->add(
                action: $container->get(JobSetDefaults::class),
                position: 53,
            );

            //After SaveJob
            $previous->add(
                action: $container->get(JobUpdaterNotifier::class),
                position: 65,
            );

            $previous->addErrorHandler($container->get(JobErrorNotifier::class));

            return $previous;
        }
    ),

    JobStart::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(LoadObject::class),
            diGet(ObjectAccessControlInterface::class),
            diGet(LoadAccountFromProject::class),
            diGet(CreateObject::class),
            diGet(PrepareNewJobForm::class),
            diGet(LoadPersistedVariablesForJob::class),
            diGet(LoadAccountClusters::class),
            diGet(FormHandlingInterface::class),
            diGet(FormProcessingInterface::class),
            diGet(NewJobSetDefaults::class),
            diGet(PersistJobVar::class),
            diGet(NewJobNotifierInterface::class),
            diGet(JumpIf::class),
            diGet(CallNewJobInterface::class),
            diGet(RedirectClientInterface::class),
            diGet(RenderFormInterface::class),
            diGet(RenderError::class),
            diGet('teknoo.east.common.get_default_error_template'),
        ),

    JobList::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(LoadObject::class),
            diGet(ExtractPage::class),
            diGet(ExtractOrder::class),
            diGet(JobPrepareCriteria::class),
            diGet(LoadListObjects::class),
            diGet(RenderList::class),
            diGet(RenderError::class),
            diGet(SearchFormLoaderInterface::class),
            diGet(ListObjectsAccessControlInterface::class),
            diGet('teknoo.east.common.get_default_error_template'),
        ),

    JobRestart::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(LoadObject::class),
            diGet(ObjectAccessControlInterface::class),
            diGet(CreateObject::class),
            diGet(LoadPersistedVariablesForJob::class),
            diGet(PrepareNewJobForm::class),
            diGet(FormHandlingInterface::class),
            diGet(RenderFormInterface::class),
            diGet(RenderError::class),
            diGet('teknoo.east.common.get_default_error_template'),
        ),

    JobPending::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(LoadObject::class),
            diGet(ObjectAccessControlInterface::class),
            diGet(FetchJobIdFromPendingInterface::class),
            diGet(Render::class),
            diGet(RenderError::class),
            diGet('teknoo.east.common.get_default_error_template'),
        ),

    JobGet::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(LoadObject::class),
            diGet(ExtractProject::class),
            diGet(LoadProjectMetadata::class),
            diGet(InjectToViewMetadata::class),
            diGet(Render::class),
            diGet(RenderError::class),
            diGet(ObjectAccessControlInterface::class),
            diGet('teknoo.east.common.get_default_error_template'),
        ),

    UserMySettings::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(FormHandlingInterface::class),
            diGet(FormProcessingInterface::class),
            diGet(SaveObject::class),
            diGet(RenderFormInterface::class),
            diGet(RenderError::class),
            value(SpaceUser::class),
            diGet('teknoo.east.common.get_default_error_template'),
        ),

    UserManageApiTokens::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(CreateObject::class),
            diGet(FormHandlingInterface::class),
            diGet(FormProcessingInterface::class),
            diGet(SaveObject::class),
            diGet(RenderFormInterface::class),
            diGet(RenderError::class),
            diGet('teknoo.east.common.get_default_error_template'),
        ),

    UserDeleteApiToken::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(RemoveKey::class),
            diGet(SaveObject::class),
            diGet(RedirectClientInterface::class),
            diGet(RenderError::class),
            diGet('teknoo.east.common.get_default_error_template'),
        ),

    UserCreateFromFormJwtToken::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(CreateObject::class),
            diGet(FormHandlingInterface::class),
            diGet(FormProcessingInterface::class),
            diGet(JwtCreateTokenInterface::class),
            diGet(Render::class),
            diGet(Stop::class),
            diGet(RenderFormInterface::class),
            diGet(RenderError::class),
            diGet('teknoo.east.common.get_default_error_template'),
        ),

    UserCreateJwtToken::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(CreateObject::class),
            diGet(JwtCreateTokenInterface::class),
            diGet(Render::class),
            diGet(RenderError::class),
            diGet('teknoo.east.common.get_default_error_template'),
        ),

    AccountEditSettings::class => static function (ContainerInterface $container): AccountEditSettings {
        $plan = new AccountEditSettings(
            $container->get(OriginalRecipeInterface::class),
            $container->get(FormHandlingInterface::class),
            $container->get(FormProcessingInterface::class),
            $container->get(SaveObject::class),
            $container->get(RenderFormInterface::class),
            $container->get(RenderError::class),
            SpaceAccount::class,
            $container->get('teknoo.east.common.get_default_error_template'),
        );

        $addStepsToManageEnv = $container->get('teknoo.space.account.additional_steps.common');
        return $addStepsToManageEnv($plan, $container);
    },

    //Special plan to edit account's settings without additional steps, like for variables
    AccountEditSettings::class . ':without-steps' => create(AccountEditSettings::class)
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(FormHandlingInterface::class),
            diGet(FormProcessingInterface::class),
            diGet(SaveObject::class),
            diGet(RenderFormInterface::class),
            diGet(RenderError::class),
            value(SpaceAccount::class),
            diGet('teknoo.east.common.get_default_error_template'),
        ),

    EditAccountEndPointInterface::class . ':without-steps' => static function (
        ContainerInterface $container
    ): EditAccountEndPointInterface {
        $accessControl = null;
        if ($container->has(ObjectAccessControlInterface::class)) {
            $accessControl = $container->get(ObjectAccessControlInterface::class);
        }

        $defaultErrorTemplate = null;
        if ($container->has('teknoo.east.common.get_default_error_template')) {
            $defaultErrorTemplate = $container->get('teknoo.east.common.get_default_error_template');
        }

        return new class (
            $container->get(OriginalRecipeInterface::class),
            $container->get(LoadObject::class),
            $container->get(FormHandlingInterface::class),
            $container->get(FormProcessingInterface::class),
            $container->get(SaveObject::class),
            $container->get(RenderFormInterface::class),
            $container->get(RenderError::class),
            $accessControl,
            $defaultErrorTemplate,
        ) extends AbstractEditObjectEndPoint implements EditAccountEndPointInterface {
        };
    },

    ProjectNew::class => static function (ContainerInterface $container): ProjectNew {
        $plan = new ProjectNew(
            $container->get(OriginalRecipeInterface::class),
            $container->get(ObjectAccessControlInterface::class),
            $container->get(CreateObject::class),
            $container->get(FormHandlingInterface::class),
            $container->get(FormProcessingInterface::class),
            $container->get(SaveObject::class),
            $container->get(RedirectClientInterface::class),
            $container->get(RenderFormInterface::class),
            $container->get(RenderError::class),
            $container->get('teknoo.east.common.get_default_error_template'),
        );

        $steps = $container->get('teknoo.space.project.endpoint.new.additional_steps');

        foreach ($steps as $class => $position) {
            $plan->add($container->get($class), $position);
        }

        return $plan;
    },

    ProjectList::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(ExtractPage::class),
            diGet(ExtractOrder::class),
            diGet(LoadAccountFromRequest::class),
            diGet(LoadEnvironments::class),
            diGet(LoadSubscriptionPlan::class),
            diGet(CreateResumes::class),
            diGet(ProjectPrepareCriteria::class),
            diGet(InjectStatus::class),
            diGet(LoadListObjects::class),
            diGet(RenderList::class),
            diGet(RenderError::class),
            diGet(SearchFormLoaderInterface::class),
            diGet(ListObjectsAccessControlInterface::class),
            diGet('teknoo.east.common.get_default_error_template'),
            value([]),
        ),

    RefreshProjectCredentials::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(LoadObject::class),
            diGet(ObjectAccessControlInterface::class),
            diGet(LoadAccountFromProject::class),
            diGet(LoadEnvironments::class),
            diGet(LoadAccountClusters::class),
            diGet(LoadRegistryCredential::class),
            diGet(UpdateProjectCredentialsFromAccount::class),
            diGet(SaveObject::class),
            diGet(SpaceProjectPrepareRedirection::class),
            diGet(RedirectClientInterface::class),
            diGet(RenderError::class),
            diGet('teknoo.east.common.get_default_error_template'),
        ),

    AccountClusterEdit::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class . ':CRUD'),
            diGet(JumpIfNot::class),
            diGet(LoadObject::class),
            diGet(FormHandlingInterface::class),
            diGet(FormProcessingInterface::class),
            diGet(SaveObject::class),
            diGet(InjectToView::class),
            diGet(RenderFormInterface::class),
            diGet(RenderError::class),
            diGet(ObjectAccessControlInterface::class),
            diGet('teknoo.east.common.get_default_error_template'),
        ),

    AccountClusterNew::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class . ':CRUD'),
            diGet(JumpIfNot::class),
            diGet(LoadObject::class),
            diGet(ObjectAccessControlInterface::class),
            diGet(CreateObject::class),
            diGet(FormHandlingInterface::class),
            diGet(FormProcessingInterface::class),
            diGet(SaveObject::class),
            diGet(InjectToView::class),
            diGet(RedirectClientInterface::class),
            diGet(RenderFormInterface::class),
            diGet(RenderError::class),
            diGet('teknoo.east.common.get_default_error_template'),
        ),

    AccountClusterDelete::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class . ':CRUD'),
            diGet(JumpIfNot::class),
            diGet(LoadObject::class),
            diGet(InjectToView::class),
            diGet(DeleteObject::class),
            diGet(JumpIf::class),
            diGet(RedirectClientInterface::class),
            diGet(Render::class),
            diGet(RenderError::class),
            diGet(ObjectAccessControlInterface::class),
            diGet('teknoo.east.common.get_default_error_template'),
        ),

    AccountClusterList::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class . ':CRUD'),
            diGet(ExtractPage::class),
            diGet(ExtractOrder::class),
            diGet(JumpIfNot::class),
            diGet(LoadObject::class),
            diGet(ObjectAccessControlInterface::class),
            diGet(ProjectPrepareCriteria::class),
            diGet(LoadListObjects::class),
            diGet(InjectToView::class),
            diGet(RenderList::class),
            diGet(RenderError::class),
            diGet(SearchFormLoaderInterface::class),
            diGet(ListObjectsAccessControlInterface::class),
            diGet('teknoo.east.common.get_default_error_template'),
            value([]),
        ),

    Dashboard::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(HealthInterface::class),
            diGet(LoadEnvironments::class),
            diGet(ClustersInfoInterface::class),
            diGet(ClusterAndEnvSelection::class),
            diGet(Render::class),
            diGet(RenderError::class),
            diGet('teknoo.east.common.get_default_error_template'),
        ),

    DashboardFrame::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(LoadEnvironments::class),
            diGet(DashboardFrameInterface::class),
            diGet(RenderError::class),
            diGet('teknoo.east.common.get_default_error_template'),
        ),

    Contact::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(CreateObject::class),
            diGet(FormHandlingInterface::class),
            diGet(FormProcessingInterface::class),
            diGet(SendEmailInterface::class),
            diGet(RedirectClientInterface::class),
            diGet(RenderFormInterface::class),
            diGet(RenderError::class),
            diGet('teknoo.east.common.get_default_error_template'),
        ),

    AdminAccountStatus::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(LoadObject::class),
            diGet(LoadSubscriptionPlan::class),
            diGet(LoadEnvironments::class),
            diGet(CreateResumes::class),
            diGet(InjectStatus::class),
            diGet(Render::class),
            diGet(RenderError::class),
            diGet('teknoo.east.common.get_default_error_template'),
        ),

    AccountStatus::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(LoadSubscriptionPlan::class),
            diGet(LoadEnvironments::class),
            diGet(CreateResumes::class),
            diGet(InjectStatus::class),
            diGet(Render::class),
            diGet(RenderError::class),
            diGet('teknoo.east.common.get_default_error_template'),
        )
];
