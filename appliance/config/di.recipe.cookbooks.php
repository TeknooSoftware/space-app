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
use Teknoo\East\Common\Recipe\Step\ExtractOrder;
use Teknoo\East\Common\Recipe\Step\ExtractPage;
use Teknoo\East\Common\Recipe\Step\JumpIf;
use Teknoo\East\Common\Recipe\Step\LoadListObjects;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\RenderList;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\East\Common\Recipe\Step\Stop;
use Teknoo\East\Paas\Contracts\Recipe\Step\Additional\EditAccountEndPointStepsInterface;
use Teknoo\East\Paas\Contracts\Recipe\Step\Additional\EditProjectEndPointStepsInterface;
use Teknoo\East\Paas\Contracts\Recipe\Step\Additional\NewAccountEndPointStepsInterface;
use Teknoo\East\Paas\Contracts\Recipe\Step\Additional\NewJobErrorsHandlersInterface;
use Teknoo\East\Paas\Contracts\Recipe\Step\Additional\NewJobStepsInterface;
use Teknoo\East\Paas\Contracts\Recipe\Step\Additional\NewProjectEndPointStepsInterface;
use Teknoo\East\Paas\Contracts\Recipe\Step\Additional\RunJobStepsInterface;
use Teknoo\Recipe\Bowl\RecipeBowl;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;
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
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Cookbook\AccountInstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Cookbook\AccountRefreshQuota;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Cookbook\AccountRegistryReinstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Cookbook\AccountReinstall;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateNamespace;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateQuota;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateRegistryAccount;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateRole;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateRoleBinding;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateSecretServiceAccountToken;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateServiceAccount;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\CreateStorage;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\PrepareAccountErrorHandler;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReinstallAccountErrorHandler;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReloadNamespace;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Client\SetRedirectClientAtEnd;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Job\JobErrorNotifier;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Job\JobUpdaterNotifier;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Job\PersistJobVar;
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
use Teknoo\Space\Recipe\Step\Account\SetQuota;
use Teknoo\Space\Recipe\Step\Account\UpdateAccountHistory;
use Teknoo\Space\Recipe\Step\AccountCredential\LoadCredentials;
use Teknoo\Space\Recipe\Step\AccountCredential\PersistCredentials;
use Teknoo\Space\Recipe\Step\AccountCredential\RemoveCredentials;
use Teknoo\Space\Recipe\Step\AccountCredential\UpdateCredentials;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;
use Teknoo\Space\Recipe\Step\Job\ExtractProject;
use Teknoo\Space\Recipe\Step\Job\IncludeExtraInWorkplan;
use Teknoo\Space\Recipe\Step\Job\JobSetDefaults;
use Teknoo\Space\Recipe\Step\Job\PrepareCriteria as JobPrepareCriteria;
use Teknoo\Space\Recipe\Step\Job\PrepareNewJobForm;
use Teknoo\Space\Recipe\Step\NewJob\NewJobSetDefaults;
use Teknoo\Space\Recipe\Step\PersistedVariable\LoadPersistedVariablesForJob;
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
use function DI\get;
use function DI\value;

return array(
    Subscription::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class),
            get(CreateObject::class),
            get(FormHandlingInterface::class),
            get(FormProcessingInterface::class),
            get(CreateUserInterface::class),
            get(CreateAccountInterface::class),
            get(NewAccountEndPointStepsInterface::class),
            get(LoginUserInterface::class),
            get(RenderFormInterface::class),
            get(RenderError::class),
            get('teknoo.east.common.cookbook.default_error_template'),
        ),

    FormWithoutObject::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class),
            get(CreateObject::class),
            get(FormHandlingInterface::class),
            get(RenderFormInterface::class),
            get(RenderError::class),
            get('teknoo.east.common.cookbook.default_error_template'),
        ),

    AccountInstall::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class),
            get(CreateNamespace::class),
            get(CreateServiceAccount::class),
            get(CreateQuota::class),
            get(CreateRole::class),
            get(CreateRoleBinding::class),
            get(CreateSecretServiceAccountToken::class),
            get(CreateStorage::class),
            get(CreateRegistryAccount::class),
            get(PersistCredentials::class),
            get(PrepareAccountErrorHandler::class),
            get('teknoo.east.paas.default_storage_size'),
        ),

    AccountReinstall::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class),
            get(LoadObject::class),
            get(AccountPrepareRedirection::class),
            get(SetRedirectClientAtEnd::class),
            get(LoadHistory::class),
            get(LoadCredentials::class),
            get(RemoveCredentials::class),
            get(SetAccountNamespace::class),
            get(AccountInstall::class),
            get(UpdateAccountHistory::class),
            get(ReinstallAccountErrorHandler::class),
            get(ObjectAccessControlInterface::class),
            get('teknoo.east.paas.default_storage_size'),
        ),

    AccountRefreshQuota::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class),
            get(LoadObject::class),
            get(AccountPrepareRedirection::class),
            get(SetRedirectClientAtEnd::class),
            get(LoadHistory::class),
            get(LoadCredentials::class),
            get(ReloadNamespace::class),
            get(CreateQuota::class),
            get(UpdateAccountHistory::class),
            get(ReinstallAccountErrorHandler::class),
            get(ObjectAccessControlInterface::class),
        ),

    AccountRegistryReinstall::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class),
            get(LoadObject::class),
            get(AccountPrepareRedirection::class),
            get(SetRedirectClientAtEnd::class),
            get(LoadHistory::class),
            get(LoadCredentials::class),
            get(ReloadNamespace::class),
            get(CreateStorage::class),
            get(CreateRegistryAccount::class),
            get(UpdateCredentials::class),
            get(UpdateAccountHistory::class),
            get(ReinstallAccountErrorHandler::class),
            get(ObjectAccessControlInterface::class),
            get('teknoo.east.paas.default_storage_size'),
        ),

    NewAccountEndPointStepsInterface::class => decorate(
        static function (
            NewAccountEndPointStepsInterface $previous,
            ContainerInterface $container,
        ): NewAccountEndPointStepsInterface {
            $previous->add(54, $container->get(ExtractFromAccountDTO::class));
            $previous->add(55, $container->get(SetAccountNamespace::class));
            $previous->add(55, $container->get(SetQuota::class));
            $previous->add(61, $container->get(CreateAccountHistory::class));
            $previous->add(62, new RecipeBowl($container->get(AccountInstall::class), 0));
            $previous->add(69, $container->get(UpdateAccountHistory::class));

            return $previous;
        }
    ),

    NewProjectEndPointStepsInterface::class => decorate(
        static function (
            NewProjectEndPointStepsInterface $previous,
            ContainerInterface $container
        ): NewProjectEndPointStepsInterface {
            $previous->add(06, $container->get(LoadCredentials::class));
            $previous->add(11, $container->get(WorkplanInit::class));
            $previous->add(15, $container->get(PrepareProject::class));
            $previous->add(69, $container->get(SpaceProjectPrepareRedirection::class));

            return $previous;
        }
    ),

    EditAccountEndPointStepsInterface::class => decorate(
        static function (
            EditAccountEndPointStepsInterface $previous,
            ContainerInterface $container
        ): EditAccountEndPointStepsInterface {
            $previous->add(11, $container->get(ExtractFromAccountDTO::class));
            $previous->add(25, $container->get(LoadHistory::class));
            $previous->add(58, $container->get(SetAccountNamespace::class));
            $previous->add(58, $container->get(SetQuota::class));
            $previous->add(59, $container->get(CreateAccountHistory::class));

            return $previous;
        }
    ),

    EditProjectEndPointStepsInterface::class => decorate(
        static function (
            EditProjectEndPointStepsInterface $previous,
            ContainerInterface $container
        ): EditProjectEndPointStepsInterface {
            $previous->add(11, $container->get(WorkplanInit::class));

            return $previous;
        }
    ),

    NewJobStepsInterface::class => decorate(
        static function (NewJobStepsInterface $previous, ContainerInterface $container): NewJobStepsInterface {
            $previous->add(51, $container->get(LoadAccountFromProject::class));
            $previous->add(52, $container->get(LoadCredentials::class));
            $previous->add(53, $container->get(JobSetDefaults::class));
            $previous->add(65, $container->get(JobUpdaterNotifier::class));
            $previous->add(75, $container->get(PersistJobVar::class));

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
            get(OriginalRecipeInterface::class),
            get(LoadObject::class),
            get(ObjectAccessControlInterface::class),
            get(CreateObject::class),
            get(PrepareNewJobForm::class),
            get(LoadPersistedVariablesForJob::class),
            get(FormHandlingInterface::class),
            get(FormProcessingInterface::class),
            get(NewJobSetDefaults::class),
            get(NewJobNotifierInterface::class),
            get(JumpIf::class),
            get(CallNewJobInterface::class),
            get(RedirectClientInterface::class),
            get(RenderFormInterface::class),
            get(RenderError::class),
            get('teknoo.east.common.cookbook.default_error_template'),
        ),

    JobList::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class),
            get(LoadObject::class),
            get(ExtractPage::class),
            get(ExtractOrder::class),
            get(JobPrepareCriteria::class),
            get(LoadListObjects::class),
            get(RenderList::class),
            get(RenderError::class),
            get(SearchFormLoaderInterface::class),
            get(ListObjectsAccessControlInterface::class),
            get('teknoo.east.common.cookbook.default_error_template'),
        ),

    JobRestart::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class),
            get(LoadObject::class),
            get(ObjectAccessControlInterface::class),
            get(CreateObject::class),
            get(LoadPersistedVariablesForJob::class),
            get(PrepareNewJobForm::class),
            get(FormHandlingInterface::class),
            get(RenderFormInterface::class),
            get(RenderError::class),
            get('teknoo.east.common.cookbook.default_error_template'),
        ),

    JobPending::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class),
            get(LoadObject::class),
            get(ObjectAccessControlInterface::class),
            get(FetchJobIdFromPendingInterface::class),
            get(Render::class),
            get(RenderError::class),
            get('teknoo.east.common.cookbook.default_error_template'),
        ),

    JobGet::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class),
            get(LoadObject::class),
            get(ExtractProject::class),
            get(LoadProjectMetadata::class),
            get(InjectToViewMetadata::class),
            get(Render::class),
            get(RenderError::class),
            get(ObjectAccessControlInterface::class),
            get('teknoo.east.common.cookbook.default_error_template'),
        ),

    UserMySettings::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class),
            get(FormHandlingInterface::class),
            get(FormProcessingInterface::class),
            get(SaveObject::class),
            get(RenderFormInterface::class),
            get(RenderError::class),
            value(SpaceUser::class),
            get('teknoo.east.common.cookbook.default_error_template'),
        ),

    UserGetJwtToken::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class),
            get(CreateObject::class),
            get(FormHandlingInterface::class),
            get(FormProcessingInterface::class),
            get(JwtCreateTokenInterface::class),
            get(Render::class),
            get(Stop::class),
            get(RenderFormInterface::class),
            get(RenderError::class),
            get('teknoo.east.common.cookbook.default_error_template'),
        ),

    AccountEditSettings::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class),
            get(FormHandlingInterface::class),
            get(FormProcessingInterface::class),
            get(SaveObject::class),
            get(RenderFormInterface::class),
            get(RenderError::class),
            value(SpaceAccount::class),
            get('teknoo.east.common.cookbook.default_error_template'),
        ),

    ProjectNew::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class),
            get(ObjectAccessControlInterface::class),
            get(CreateObject::class),
            get(FormHandlingInterface::class),
            get(FormProcessingInterface::class),
            get(SaveObject::class),
            get(RedirectClientInterface::class),
            get(RenderFormInterface::class),
            get(RenderError::class),
            get(NewProjectEndPointStepsInterface::class),
            get('teknoo.east.common.cookbook.default_error_template'),
        ),

    ProjectList::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class),
            get(ExtractPage::class),
            get(ExtractOrder::class),
            get(ProjectPrepareCriteria::class),
            get(LoadListObjects::class),
            get(RenderList::class),
            get(RenderError::class),
            get(SearchFormLoaderInterface::class),
            get(ListObjectsAccessControlInterface::class),
            get('teknoo.east.common.cookbook.default_error_template'),
            value([]),
        ),

    RefreshProjectCredentials::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class),
            get(LoadObject::class),
            get(ObjectAccessControlInterface::class),
            get(LoadAccountFromProject::class),
            get(LoadCredentials::class),
            get(UpdateProjectCredentialsFromAccount::class),
            get(SaveObject::class),
            get(SpaceProjectPrepareRedirection::class),
            get(RedirectClientInterface::class),
            get(RenderError::class),
            get('teknoo.east.common.cookbook.default_error_template'),
        ),

    Dashboard::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class),
            get(HealthInterface::class),
            get(DashboardInfoInterface::class),
            get(Render::class),
            get(RenderError::class),
            get('teknoo.east.common.cookbook.default_error_template'),
        ),

    DashboardFrame::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class),
            get(LoadCredentials::class),
            get(DashboardFrameInterface::class),
            get(RenderError::class),
            get('teknoo.east.common.cookbook.default_error_template'),
        ),

    Contact::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class),
            get(CreateObject::class),
            get(FormHandlingInterface::class),
            get(FormProcessingInterface::class),
            get(SendEmailInterface::class),
            get(RedirectClientInterface::class),
            get(RenderFormInterface::class),
            get(RenderError::class),
            get('teknoo.east.common.cookbook.default_error_template'),
        ),
);
