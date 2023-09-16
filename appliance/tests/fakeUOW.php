<?php

namespace Doctrine\ODM\MongoDB;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;

if (!\class_exists(UnitOfWork::class, false)) {
    class UnitOfWork
    {
        public function propertyChanged($sender, $propertyName, $oldValue, $newValue)
        {
        }

        public function getDocumentChangeSet(object $document): array
        {
        }

        public function clearDocumentChangeSet(string $oid)
        {
        }

        public function recomputeSingleDocumentChangeSet(ClassMetadata $class, object $document): void
        {
        }

        public function setOriginalDocumentProperty(string $oid, string $property, $value): void
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

        public function getDocumentPersister(string $className)
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
