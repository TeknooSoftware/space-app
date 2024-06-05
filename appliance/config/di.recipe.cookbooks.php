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

use Psr\Container\ContainerInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ListObjectsAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\SearchFormLoaderInterface;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\EndLooping;
use Teknoo\East\Common\Recipe\Step\ExtractOrder;
use Teknoo\East\Common\Recipe\Step\ExtractPage;
use Teknoo\East\Common\Recipe\Step\JumpIf;
use Teknoo\East\Common\Recipe\Step\LoadListObjects;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\RenderList;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\East\Common\Recipe\Step\StartLoopingOn;
use Teknoo\East\Common\Recipe\Step\Stop;
use Teknoo\East\Paas\Contracts\Recipe\AdditionalStepsInterface;
use Teknoo\East\Paas\Contracts\Recipe\Cookbook\EditAccountEndPointInterface;
use Teknoo\East\Paas\Contracts\Recipe\Step\Additional\EditAccountEndPointStepsInterface;
use Teknoo\East\Paas\Contracts\Recipe\Step\Additional\EditProjectEndPointStepsInterface;
use Teknoo\East\Paas\Contracts\Recipe\Step\Additional\NewAccountEndPointStepsInterface;
use Teknoo\East\Paas\Contracts\Recipe\Step\Additional\NewJobErrorsHandlersInterface;
use Teknoo\East\Paas\Contracts\Recipe\Step\Additional\NewJobStepsInterface;
use Teknoo\East\Paas\Contracts\Recipe\Step\Additional\NewProjectEndPointStepsInterface;
use Teknoo\East\Paas\Contracts\Recipe\Step\Additional\RunJobStepsInterface;
use Teknoo\East\Paas\Recipe\AbstractAdditionalStepsList;
use Teknoo\East\Paas\Recipe\Cookbook\AbstractEditObjectEndPoint;
use Teknoo\East\Paas\Recipe\Step;
use Teknoo\Recipe\Bowl\RecipeBowl;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;
use Teknoo\Recipe\Value;
use Teknoo\Space\Contracts\Recipe\Step\Additional\EditAccountSettingsEndPointStepsInterface;
use Teknoo\Space\Contracts\Recipe\Step\Contact\SendEmailInterface;
use Teknoo\Space\Contracts\Recipe\Step\Job\CallNewJobInterface;
use Teknoo\Space\Contracts\Recipe\Step\Job\FetchJobIdFromPendingInterface;
use Teknoo\Space\Contracts\Recipe\Step\Job\NewJobNotifierInterface;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\DashboardFrameInterface;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\DashboardInfoInterface;
use Teknoo\Space\Contracts\Recipe\Step\Kubernetes\HealthInterface;
use Teknoo\Space\Contracts\Recipe\Step\Subscription\CreateAccountInterface;
use Teknoo\Space\Contracts\Recipe\Step\Subscription\CreateUserInterface;
use Teknoo\Space\Contracts\Recipe\Step\Subscription\LoginUserInterface;
use Teknoo\Space\Contracts\Recipe\Step\User\JwtCreateTokenInterface;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Cookbook\AccountEnvironmentInstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Cookbook\AccountEnvironmentReinstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Cookbook\AccountRefreshQuota;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Cookbook\AccountRegistryInstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Cookbook\AccountRegistryReinstall;
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
use Teknoo\Space\Recipe\Cookbook\AccountEditSettings;
use Teknoo\Space\Recipe\Cookbook\Contact;
use Teknoo\Space\Recipe\Cookbook\Dashboard;
use Teknoo\Space\Recipe\Cookbook\DashboardFrame;
use Teknoo\Space\Recipe\Cookbook\FormWithoutObject;
use Teknoo\Space\Recipe\Cookbook\JobGet;
use Teknoo\Space\Recipe\Cookbook\JobList;
use Teknoo\Space\Recipe\Cookbook\JobPending;
use Teknoo\Space\Recipe\Cookbook\JobRestart;
use Teknoo\Space\Recipe\Cookbook\JobStart;
use Teknoo\Space\Recipe\Cookbook\ProjectList;
use Teknoo\Space\Recipe\Cookbook\ProjectNew;
use Teknoo\Space\Recipe\Cookbook\RefreshProjectCredentials;
use Teknoo\Space\Recipe\Cookbook\Subscription;
use Teknoo\Space\Recipe\Cookbook\UserGetJwtToken;
use Teknoo\Space\Recipe\Cookbook\UserMySettings;
use Teknoo\Space\Recipe\Step\Account\CreateAccountHistory;
use Teknoo\Space\Recipe\Step\Account\ExtractFromAccountDTO;
use Teknoo\Space\Recipe\Step\Account\PrepareRedirection as AccountPrepareRedirection;
use Teknoo\Space\Recipe\Step\Account\SetAccountNamespace;
use Teknoo\Space\Recipe\Step\Account\SetPlan;
use Teknoo\Space\Recipe\Step\Account\SetQuota;
use Teknoo\Space\Recipe\Step\Account\UpdateAccountHistory;
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
use Teknoo\Space\Recipe\Step\ClusterConfig\SelectClusterConfig;
use Teknoo\Space\Recipe\Step\Job\ExtractProject;
use Teknoo\Space\Recipe\Step\Job\IncludeExtraInWorkplan;
use Teknoo\Space\Recipe\Step\Job\JobSetDefaults;
use Teknoo\Space\Recipe\Step\Job\PrepareCriteria as JobPrepareCriteria;
use Teknoo\Space\Recipe\Step\Job\PrepareNewJobForm;
use Teknoo\Space\Recipe\Step\NewJob\NewJobSetDefaults;
use Teknoo\Space\Recipe\Step\PersistedVariable\LoadPersistedVariablesForJob;
use Teknoo\Space\Recipe\Step\Project\AddManagedEnvironmentToProject;
use Teknoo\Space\Recipe\Step\Project\LoadAccountFromProject;
use Teknoo\Space\Recipe\Step\Project\PrepareCriteria as ProjectPrepareCriteria;
use Teknoo\Space\Recipe\Step\Project\PrepareProject;
use Teknoo\Space\Recipe\Step\Project\UpdateProjectCredentialsFromAccount;
use Teknoo\Space\Recipe\Step\ProjectMetadata\InjectToViewMetadata;
use Teknoo\Space\Recipe\Step\ProjectMetadata\LoadProjectMetadata;
use Teknoo\Space\Recipe\Step\SpaceProject\PrepareRedirection as SpaceProjectPrepareRedirection;
use Teknoo\Space\Recipe\Step\SpaceProject\WorkplanInit;

use function DI\create;
use function DI\decorate;
use function DI\get as diGet;
use function DI\value;

return array(
    Subscription::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(CreateObject::class),
            diGet(FormHandlingInterface::class),
            diGet(FormProcessingInterface::class),
            diGet(CreateUserInterface::class),
            diGet(CreateAccountInterface::class),
            diGet(NewAccountEndPointStepsInterface::class),
            diGet(LoginUserInterface::class),
            diGet(RenderFormInterface::class),
            diGet(RenderError::class),
            diGet('teknoo.east.common.cookbook.default_error_template'),
        ),

    FormWithoutObject::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(CreateObject::class),
            diGet(FormHandlingInterface::class),
            diGet(RenderFormInterface::class),
            diGet(RenderError::class),
            diGet('teknoo.east.common.cookbook.default_error_template'),
        ),

    AccountEnvironmentInstall::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
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
            diGet('teknoo.east.paas.default_storage_size'),
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
            diGet('teknoo.east.paas.default_storage_size'),
        ),

    AccountRegistryInstall::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(CreateNamespace::class),
            diGet(CreateStorage::class),
            diGet(CreateRegistryDeployment::class),
            diGet(PersistRegistryCredential::class),
            diGet(PrepareAccountErrorHandler::class),
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

    NewAccountEndPointStepsInterface::class => decorate(
        static function (
            NewAccountEndPointStepsInterface $previous,
            ContainerInterface $container,
        ): NewAccountEndPointStepsInterface {
            //After ObjectAccessControlInterface
            $previous->add(54, $container->get(ExtractFromAccountDTO::class));
            $previous->add(55, $container->get(SetAccountNamespace::class));
            $previous->add(55, $container->get(SetPlan::class));
            $previous->add(55, $container->get(SetQuota::class));
            //After SaveObject
            $previous->add(61, $container->get(CreateAccountHistory::class));
            $previous->add(62, new RecipeBowl($container->get(AccountRegistryInstall::class), 0));
            $previous->add(69, $container->get(UpdateAccountHistory::class));
            //After RedirectClientInterface

            return $previous;
        }
    ),

    NewProjectEndPointStepsInterface::class => decorate(
        static function (
            NewProjectEndPointStepsInterface $previous,
            ContainerInterface $container
        ): NewProjectEndPointStepsInterface {
            //Before CreateObject
            $previous->add(06, $container->get(LoadRegistryCredential::class));
            $previous->add(06, $container->get(LoadEnvironments::class));
            $previous->add(07, $container->get(CreateResumes::class));
            //After CreateObject
            $previous->add(11, $container->get(WorkplanInit::class));
            $previous->add(15, $container->get(PrepareProject::class));
            //After ObjectAccessControlInterface
            $previous->add(57, $container->get(LoadRegistryCredential::class));
            $previous->add(58, $container->get(AddManagedEnvironmentToProject::class));
            $previous->add(59, $container->get(UpdateProjectCredentialsFromAccount::class));
            //After SaveObject
            $previous->add(69, $container->get(SpaceProjectPrepareRedirection::class));

            return $previous;
        }
    ),

    'teknoo.space.account.additional_steps.common' => static function (): callable {
        return static function (
            AdditionalStepsInterface $steps,
            ContainerInterface $container,
        ): AdditionalStepsInterface {
            //After LoadObject
            $steps->add(11, $container->get(ExtractFromAccountDTO::class));
            $steps->add(12, $container->get(LoadEnvironments::class));
            $steps->add(13, $container->get(CreateResumes::class));

            //After ObjectAccessControlInterface
            $steps->add(25, $container->get(LoadHistory::class));

            //Before FormHandlingInterface
            $steps->add(29, $container->get(PrepareAccountForm::class));

            //After FormProcessingInterface
            $steps->add(57, $container->get(SetPlan::class));
            $steps->add(58, $container->get(CheckingAllowedCountOfEnvs::class));
            $steps->add(59, $container->get(CreateAccountHistory::class));

            //After SaveObject
            $steps->add(61, $container->get(DeleteNamespaceFromResumes::class));
            $steps->add(61, $container->get(DeleteEnvFromResumes::class));
            $steps->add(61, $container->get(LoadRegistryCredential::class));

            $steps->add(
                priority: 62,
                step: $container->get(ExtractResumes::class),
            );

            $steps->add(
                priority: 63,
                step: new Step(
                    step: new StartLoopingOn(),
                    with: [
                        'collection' => 'accountEnvsResumes',
                    ],
                ),
            );

            $steps->add(
                priority: 64,
                step: new Step(
                    step: $container->get(JumpIf::class),
                    with: [
                        'testValue' => AccountEnvironmentResume::class,
                        'expectedJumpValue' => new Value(
                            static fn (AccountEnvironmentResume $resume) => !empty($resume->accountEnvironmentId),
                        ),
                        'nextStep' => new Value(EndLooping::class),
                    ],
                ),
            );

            $steps->add(
                priority: 65,
                step: $container->get(PrepareInstall::class),
            );

            $steps->add(
                priority: 66,
                step: new RecipeBowl(
                    recipe: $container->get(AccountEnvironmentInstall::class),
                    repeat: 0,
                ),
            );

            $steps->add(
                priority: 67,
                step: new EndLooping(),
            );

            $steps->add(68, $container->get(UpdateAccountHistory::class));
            //After FormHandlingInterface::class . ':refresh'

            return $steps;
        };
    },

    EditAccountSettingsEndPointStepsInterface::class => static function (
        ContainerInterface $container
    ): EditAccountSettingsEndPointStepsInterface {
        $list = new class extends AbstractAdditionalStepsList implements EditAccountSettingsEndPointStepsInterface {
        };

        $addStepsToManageEnv = $container->get('teknoo.space.account.additional_steps.common');
        /** @var EditAccountSettingsEndPointStepsInterface $list */
        $list = $addStepsToManageEnv($list, $container);

        return $list;
    },

    EditAccountEndPointStepsInterface::class => decorate(
        callable: static function (
            EditAccountEndPointStepsInterface $previous,
            ContainerInterface $container
        ): EditAccountEndPointStepsInterface {
            //After FormProcessingInterface
            $previous->add(58, $container->get(SetAccountNamespace::class));
            $previous->add(58, $container->get(SetQuota::class));

            $addStepsToManageEnv = $container->get('teknoo.space.account.additional_steps.common');
            /** @var EditAccountEndPointStepsInterface $previous */
            $previous = $addStepsToManageEnv($previous, $container);

            return $previous;
        }
    ),

    EditProjectEndPointStepsInterface::class => decorate(
        static function (
            EditProjectEndPointStepsInterface $previous,
            ContainerInterface $container
        ): EditProjectEndPointStepsInterface {
            //After LoadObject
            $previous->add(11, $container->get(LoadAccountFromProject::class));
            $previous->add(11, $container->get(WorkplanInit::class));
            $previous->add(12, $container->get(LoadEnvironments::class));
            $previous->add(13, $container->get(CreateResumes::class));
            //After FormProcessingInterface
            $previous->add(58, $container->get(AddManagedEnvironmentToProject::class));

            return $previous;
        }
    ),

    NewJobStepsInterface::class => decorate(
        static function (NewJobStepsInterface $previous, ContainerInterface $container): NewJobStepsInterface {
            //After PrepareJob
            $previous->add(51, $container->get(LoadAccountFromProject::class));
            $previous->add(52, $container->get(LoadEnvironments::class));
            $previous->add(52, $container->get(LoadRegistryCredential::class));
            $previous->add(53, $container->get(JobSetDefaults::class));
            //After SaveJob
            $previous->add(65, $container->get(JobUpdaterNotifier::class));

            return $previous;
        }
    ),

    RunJobStepsInterface::class => decorate(
        static function (RunJobStepsInterface $previous, ContainerInterface $container): RunJobStepsInterface {
            $previous->add(21, $container->get(IncludeExtraInWorkplan::class));

            return $previous;
        }
    ),

    NewJobErrorsHandlersInterface::class => decorate(
        static function (
            NewJobErrorsHandlersInterface $previous,
            ContainerInterface $container
        ): NewJobErrorsHandlersInterface {
            $previous->add(1, $container->get(JobErrorNotifier::class));

            return $previous;
        }
    ),

    JobStart::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(LoadObject::class),
            diGet(ObjectAccessControlInterface::class),
            diGet(CreateObject::class),
            diGet(PrepareNewJobForm::class),
            diGet(LoadPersistedVariablesForJob::class),
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
            diGet('teknoo.east.common.cookbook.default_error_template'),
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
            diGet('teknoo.east.common.cookbook.default_error_template'),
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
            diGet('teknoo.east.common.cookbook.default_error_template'),
        ),

    JobPending::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(LoadObject::class),
            diGet(ObjectAccessControlInterface::class),
            diGet(FetchJobIdFromPendingInterface::class),
            diGet(Render::class),
            diGet(RenderError::class),
            diGet('teknoo.east.common.cookbook.default_error_template'),
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
            diGet('teknoo.east.common.cookbook.default_error_template'),
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
            diGet('teknoo.east.common.cookbook.default_error_template'),
        ),

    UserGetJwtToken::class => create()
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
            diGet('teknoo.east.common.cookbook.default_error_template'),
        ),

    AccountEditSettings::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(FormHandlingInterface::class),
            diGet(FormProcessingInterface::class),
            diGet(SaveObject::class),
            diGet(RenderFormInterface::class),
            diGet(RenderError::class),
            value(SpaceAccount::class),
            diGet('teknoo.east.common.cookbook.default_error_template'),
            diGet(EditAccountSettingsEndPointStepsInterface::class),
        ),

    //Special cookbook to edit account's settings without additional steps, like for variables
    AccountEditSettings::class . ':without-steps' => create(AccountEditSettings::class)
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(FormHandlingInterface::class),
            diGet(FormProcessingInterface::class),
            diGet(SaveObject::class),
            diGet(RenderFormInterface::class),
            diGet(RenderError::class),
            value(SpaceAccount::class),
            diGet('teknoo.east.common.cookbook.default_error_template'),
        ),

    EditAccountEndPointInterface::class . ':without-steps' => static function (
        ContainerInterface $container
    ): EditAccountEndPointInterface {
        $accessControl = null;
        if ($container->has(ObjectAccessControlInterface::class)) {
            $accessControl = $container->get(ObjectAccessControlInterface::class);
        }

        $defaultErrorTemplate = null;
        if ($container->has('teknoo.east.common.cookbook.default_error_template')) {
            $defaultErrorTemplate = $container->get('teknoo.east.common.cookbook.default_error_template');
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
            [],
            $defaultErrorTemplate,
        ) extends AbstractEditObjectEndPoint implements EditAccountEndPointInterface {
        };
    },

    ProjectNew::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(ObjectAccessControlInterface::class),
            diGet(CreateObject::class),
            diGet(FormHandlingInterface::class),
            diGet(FormProcessingInterface::class),
            diGet(SaveObject::class),
            diGet(RedirectClientInterface::class),
            diGet(RenderFormInterface::class),
            diGet(RenderError::class),
            diGet(NewProjectEndPointStepsInterface::class),
            diGet('teknoo.east.common.cookbook.default_error_template'),
        ),

    ProjectList::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(ExtractPage::class),
            diGet(ExtractOrder::class),
            diGet(ProjectPrepareCriteria::class),
            diGet(LoadListObjects::class),
            diGet(RenderList::class),
            diGet(RenderError::class),
            diGet(SearchFormLoaderInterface::class),
            diGet(ListObjectsAccessControlInterface::class),
            diGet('teknoo.east.common.cookbook.default_error_template'),
            value([]),
        ),

    RefreshProjectCredentials::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(LoadObject::class),
            diGet(ObjectAccessControlInterface::class),
            diGet(LoadAccountFromProject::class),
            diGet(LoadEnvironments::class),
            diGet(LoadRegistryCredential::class),
            diGet(UpdateProjectCredentialsFromAccount::class),
            diGet(SaveObject::class),
            diGet(SpaceProjectPrepareRedirection::class),
            diGet(RedirectClientInterface::class),
            diGet(RenderError::class),
            diGet('teknoo.east.common.cookbook.default_error_template'),
        ),

    Dashboard::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(HealthInterface::class),
            diGet(LoadEnvironments::class),
            diGet(DashboardInfoInterface::class),
            diGet(Render::class),
            diGet(RenderError::class),
            diGet('teknoo.east.common.cookbook.default_error_template'),
        ),

    DashboardFrame::class => create()
        ->constructor(
            diGet(OriginalRecipeInterface::class),
            diGet(LoadEnvironments::class),
            diGet(DashboardFrameInterface::class),
            diGet(RenderError::class),
            diGet('teknoo.east.common.cookbook.default_error_template'),
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
            diGet('teknoo.east.common.cookbook.default_error_template'),
        ),
);
