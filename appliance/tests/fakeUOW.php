<?php

declare(strict_types=1);

namespace Doctrine\ODM\MongoDB;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;

use function class_exists;

if (!class_exists(UnitOfWork::class, false)) {
    class UnitOfWork
    {
        public const STATE_MANAGED = 1;

        public const STATE_NEW = 2;

        public const STATE_DETACHED = 3;

        public const STATE_REMOVED = 4;

        public const DEPRECATED_WRITE_OPTIONS = ['fsync', 'safe', 'w'];

        public function propertyChanged($sender, $propertyName, $oldValue, $newValue)
        {
        }

        public function getDocumentChangeSet(object $document): array
        {
        }

        public function clearDocumentChangeSet(int $oid)
        {
        }

        public function recomputeSingleDocumentChangeSet(ClassMetadata $class, object $document): void
        {
        }

        public function setOriginalDocumentProperty(int $oid, string $property, $value): void
        {
        }

        public function getScheduledDocumentInsertions(): array
        {
        }

        public function getScheduledDocumentUpdates(): array
        {
        }

        public function getScheduledDocumentDeletions(): array
        {
        }

        public function initializeObject(object $obj): void
        {
        }

        public function isScheduledForInsert(object $document): bool
        {
            return true;
        }

        public function getDocumentPersister(string $className): object
        {
            return new class () {
                public function addDiscriminatorToPreparedQuery(array $preparedQuery): array
                {
                    return $preparedQuery;
                }

                public function addFilterToPreparedQuery(array $preparedQuery): array
                {
                    return $preparedQuery;
                }

                public function prepareFieldName(string $fieldName): string
                {
                    return $fieldName;
                }

                public function prepareProjection(array $fields): array
                {
                    return [];
                }

                public function prepareSort(array $fields): array
                {
                    return [];
                }

                public function prepareQueryOrNewObj(array $query, bool $isNewObj = false): array
                {
                    return ['criteria' => $query, 'isNewObj' => $isNewObj];
                }
            };
        }
    }
}
