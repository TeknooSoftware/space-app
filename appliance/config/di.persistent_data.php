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

namespace App\Config;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\Persistence\ObjectManager;
use Psr\Container\ContainerInterface;
use Teknoo\East\Common\Doctrine\DBSource\Exception\NonManagedRepositoryException;
use Teknoo\East\CommonBundle\Writer\SymfonyUserWriter;
use Teknoo\East\Common\Contracts\DBSource\BatchManipulationManagerInterface;
use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Service\DatesService;
use Teknoo\East\Common\Service\DeletingService;
use Teknoo\East\Paas\Loader\AccountLoader;
use Teknoo\East\Paas\Loader\ProjectLoader;
use Teknoo\East\Paas\Writer\AccountWriter;
use Teknoo\East\Paas\Writer\ProjectWriter;
use Teknoo\Space\Contracts\DbSource\Repository\AccountCredentialRepositoryInterface;
use Teknoo\Space\Contracts\DbSource\Repository\AccountDataRepositoryInterface;
use Teknoo\Space\Contracts\DbSource\Repository\AccountHistoryRepositoryInterface;
use Teknoo\Space\Contracts\DbSource\Repository\AccountPersistedVariableRepositoryInterface;
use Teknoo\Space\Contracts\DbSource\Repository\PersistedVariableRepositoryInterface;
use Teknoo\Space\Contracts\DbSource\Repository\ProjectMetadataRepositoryInterface;
use Teknoo\Space\Contracts\DbSource\Repository\UserDataRepositoryInterface;
use Teknoo\Space\Infrastructures\Doctrine\Repository\ODM\AccountCredentialRepository;
use Teknoo\Space\Infrastructures\Doctrine\Repository\ODM\AccountDataRepository;
use Teknoo\Space\Infrastructures\Doctrine\Repository\ODM\AccountHistoryRepository;
use Teknoo\Space\Infrastructures\Doctrine\Repository\ODM\AccountPersistedVariableRepository;
use Teknoo\Space\Infrastructures\Doctrine\Repository\ODM\PersistedVariableRepository;
use Teknoo\Space\Infrastructures\Doctrine\Repository\ODM\ProjectMetadataRepository;
use Teknoo\Space\Infrastructures\Doctrine\Repository\ODM\UserDataRepository;
use Teknoo\Space\Loader\AccountCredentialLoader;
use Teknoo\Space\Loader\AccountDataLoader;
use Teknoo\Space\Loader\AccountHistoryLoader;
use Teknoo\Space\Loader\AccountPersistedVariableLoader;
use Teknoo\Space\Loader\Meta\SpaceAccountLoader;
use Teknoo\Space\Loader\Meta\SpaceProjectLoader;
use Teknoo\Space\Loader\Meta\SpaceUserLoader;
use Teknoo\Space\Loader\PersistedVariableLoader;
use Teknoo\Space\Loader\ProjectMetadataLoader;
use Teknoo\Space\Loader\UserDataLoader;
use Teknoo\Space\Object\Persisted\AccountCredential;
use Teknoo\Space\Object\Persisted\AccountData;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;
use Teknoo\Space\Object\Persisted\PersistedVariable;
use Teknoo\Space\Object\Persisted\ProjectMetadata;
use Teknoo\Space\Object\Persisted\UserData;
use Teknoo\Space\Writer\AccountCredentialWriter;
use Teknoo\Space\Writer\AccountDataWriter;
use Teknoo\Space\Writer\AccountHistoryWriter;
use Teknoo\Space\Writer\AccountPersistedVariableWriter;
use Teknoo\Space\Writer\Meta\SpaceAccountWriter;
use Teknoo\Space\Writer\Meta\SpaceProjectWriter;
use Teknoo\Space\Writer\Meta\SpaceUserWriter;
use Teknoo\Space\Writer\PersistedVariableWriter;
use Teknoo\Space\Writer\ProjectMetadataWriter;
use Teknoo\Space\Writer\UserDataWriter;

use function DI\create;
use function DI\get;

return [
    //AccountCredential
    AccountCredentialRepositoryInterface::class => get(AccountCredentialRepository::class),
    AccountCredentialRepository::class => static function (ContainerInterface $container): AccountCredentialRepository {
        $repository = $container->get(ObjectManager::class)?->getRepository(AccountCredential::class);
        if ($repository instanceof DocumentRepository) {
            return new AccountCredentialRepository($repository);
        }

        throw new NonManagedRepositoryException(sprintf(
            "Error, repository of class %s are not currently managed",
            $repository::class
        ));
    },

    AccountCredentialLoader::class => create(AccountCredentialLoader::class)
        ->constructor(get(AccountCredentialRepositoryInterface::class)),
    AccountCredentialWriter::class => create(AccountCredentialWriter::class)
        ->constructor(get(ManagerInterface::class), get(DatesService::class)),
    'teknoo.space.deleting.account_credential' => create(DeletingService::class)
        ->constructor(get(AccountCredentialWriter::class), get(DatesService::class)),

    //AccountData
    AccountDataRepositoryInterface::class => get(AccountDataRepository::class),
    AccountDataRepository::class => static function (ContainerInterface $container): AccountDataRepository {
        $repository = $container->get(ObjectManager::class)?->getRepository(AccountData::class);
        if ($repository instanceof DocumentRepository) {
            return new AccountDataRepository($repository);
        }

        throw new NonManagedRepositoryException(sprintf(
            "Error, repository of class %s are not currently managed",
            $repository::class
        ));
    },

    AccountDataLoader::class => create(AccountDataLoader::class)
        ->constructor(get(AccountDataRepositoryInterface::class)),
    AccountDataWriter::class => create(AccountDataWriter::class)
        ->constructor(get(ManagerInterface::class), get(DatesService::class)),
    'teknoo.space.deleting.account_data' => create(DeletingService::class)
        ->constructor(get(AccountDataWriter::class), get(DatesService::class)),

    //AccountHistory
    AccountHistoryRepositoryInterface::class => get(AccountHistoryRepository::class),
    AccountHistoryRepository::class => static function (ContainerInterface $container): AccountHistoryRepository {
        $repository = $container->get(ObjectManager::class)?->getRepository(AccountHistory::class);
        if ($repository instanceof DocumentRepository) {
            return new AccountHistoryRepository($repository);
        }

        throw new NonManagedRepositoryException(sprintf(
            "Error, repository of class %s are not currently managed",
            $repository::class
        ));
    },

    AccountHistoryLoader::class => create(AccountHistoryLoader::class)
        ->constructor(get(AccountHistoryRepositoryInterface::class)),
    AccountHistoryWriter::class => create(AccountHistoryWriter::class)
        ->constructor(get(ManagerInterface::class), get(DatesService::class)),
    'teknoo.space.deleting.account_history' => create(DeletingService::class)
        ->constructor(get(AccountHistoryWriter::class), get(DatesService::class)),

    //PersistedVariable
    AccountPersistedVariableRepositoryInterface::class => get(AccountPersistedVariableRepository::class),
    AccountPersistedVariableRepository::class => static function (
        ContainerInterface $container
    ): AccountPersistedVariableRepository {
        $repository = $container->get(ObjectManager::class)?->getRepository(AccountPersistedVariable::class);
        if ($repository instanceof DocumentRepository) {
            return new AccountPersistedVariableRepository($repository);
        }

        throw new NonManagedRepositoryException(sprintf(
            "Error, repository of class %s are not currently managed",
            $repository::class
        ));
    },

    PersistedVariableRepositoryInterface::class => get(PersistedVariableRepository::class),
    PersistedVariableRepository::class => static function (ContainerInterface $container): PersistedVariableRepository {
        $repository = $container->get(ObjectManager::class)?->getRepository(PersistedVariable::class);
        if ($repository instanceof DocumentRepository) {
            return new PersistedVariableRepository($repository);
        }

        throw new NonManagedRepositoryException(sprintf(
            "Error, repository of class %s are not currently managed",
            $repository::class
        ));
    },

    AccountPersistedVariableLoader::class => create(AccountPersistedVariableLoader::class)
        ->constructor(get(AccountPersistedVariableRepositoryInterface::class)),
    AccountPersistedVariableWriter::class => create(AccountPersistedVariableWriter::class)
        ->constructor(get(ManagerInterface::class), get(DatesService::class)),
    'teknoo.space.deleting.account_persisted_env_var_job' => create(DeletingService::class)
        ->constructor(get(AccountPersistedVariableWriter::class), get(DatesService::class)),

    PersistedVariableLoader::class => create(PersistedVariableLoader::class)
        ->constructor(get(PersistedVariableRepositoryInterface::class)),
    PersistedVariableWriter::class => create(PersistedVariableWriter::class)
        ->constructor(get(ManagerInterface::class), get(DatesService::class)),
    'teknoo.space.deleting.persisted_env_var_job' => create(DeletingService::class)
        ->constructor(get(PersistedVariableWriter::class), get(DatesService::class)),


    //ProjectMetadata
    ProjectMetadataRepositoryInterface::class => get(ProjectMetadataRepository::class),
    ProjectMetadataRepository::class => static function (ContainerInterface $container): ProjectMetadataRepository {
        $repository = $container->get(ObjectManager::class)?->getRepository(ProjectMetadata::class);
        if ($repository instanceof DocumentRepository) {
            return new ProjectMetadataRepository($repository);
        }

        throw new NonManagedRepositoryException(sprintf(
            "Error, repository of class %s are not currently managed",
            $repository::class
        ));
    },

    ProjectMetadataLoader::class => create(ProjectMetadataLoader::class)
        ->constructor(get(ProjectMetadataRepositoryInterface::class)),
    ProjectMetadataWriter::class => create(ProjectMetadataWriter::class)
        ->constructor(get(ManagerInterface::class), get(DatesService::class)),
    'teknoo.space.deleting.project_metadata' => create(DeletingService::class)
        ->constructor(get(ProjectMetadataWriter::class), get(DatesService::class)),

    //SpaceProject
    SpaceProjectLoader::class => create(SpaceProjectLoader::class)
        ->constructor(
            get(ProjectLoader::class),
            get(ProjectMetadataLoader::class),
            get(PersistedVariableLoader::class),
        ),
    SpaceProjectWriter::class => create(SpaceProjectWriter::class)
        ->constructor(
            get(ProjectWriter::class),
            get(ProjectMetadataWriter::class),
            get(PersistedVariableWriter::class),
            get(BatchManipulationManagerInterface::class),
        ),
    'teknoo.space.deleting.space_project' => create(DeletingService::class)
        ->constructor(
            get(SpaceProjectWriter::class),
            get(DatesService::class),
        ),

    //SpaceAccount
    SpaceAccountLoader::class => create(SpaceAccountLoader::class)
        ->constructor(
            get(AccountLoader::class),
            get(AccountDataLoader::class),
            get(AccountPersistedVariableLoader::class),
        ),
    SpaceAccountWriter::class => create(SpaceAccountWriter::class)
        ->constructor(
            get(AccountWriter::class),
            get(AccountDataWriter::class),
            get(AccountCredentialLoader::class),
            get(AccountHistoryLoader::class),
            get(AccountCredentialWriter::class),
            get(AccountHistoryWriter::class),
            get(AccountPersistedVariableWriter::class),
            get(BatchManipulationManagerInterface::class),
        ),
    'teknoo.space.deleting.space_account' => create(DeletingService::class)
        ->constructor(get(SpaceAccountWriter::class), get(DatesService::class)),

    //SpaceUser
    SpaceUserLoader::class => create(SpaceUserLoader::class)
        ->constructor(
            get(UserLoader::class),
            get(UserDataLoader::class),
        ),
    SpaceUserWriter::class => create(SpaceUserWriter::class)
        ->constructor(
            get(SymfonyUserWriter::class),
            get(UserDataWriter::class)
        ),
    'teknoo.space.deleting.space_user' => create(DeletingService::class)
        ->constructor(get(SpaceUserWriter::class), get(DatesService::class)),

    //UserData
    UserDataRepositoryInterface::class => get(UserDataRepository::class),
    UserDataRepository::class => static function (ContainerInterface $container): UserDataRepository {
        $repository = $container->get(ObjectManager::class)?->getRepository(UserData::class);
        if ($repository instanceof DocumentRepository) {
            return new UserDataRepository($repository);
        }

        throw new NonManagedRepositoryException(sprintf(
            "Error, repository of class %s are not currently managed",
            $repository::class
        ));
    },

    UserDataLoader::class => create(UserDataLoader::class)
        ->constructor(get(UserDataRepositoryInterface::class)),
    UserDataWriter::class => create(UserDataWriter::class)
        ->constructor(get(ManagerInterface::class), get(DatesService::class)),
    'teknoo.space.deleting.user_data' => create(DeletingService::class)
        ->constructor(get(UserDataWriter::class), get(DatesService::class)),
];
