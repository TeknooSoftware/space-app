<?php

declare(strict_types=1);

namespace Doctrine\ODM\MongoDB\Query;

use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Iterator\IterableResult;
use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Teknoo\Space\Tests\Behat\SpaceContext;

use function count;
use function is_iterable;

if (!\class_exists(Query::class, false)) {
    class Query implements IterableResult
    {
        final public const int TYPE_FIND            = 1;

        final public const int TYPE_FIND_AND_UPDATE = 2;

        final public const int TYPE_FIND_AND_REMOVE = 3;

        final public const int TYPE_INSERT          = 4;

        final public const int TYPE_UPDATE          = 5;

        final public const int TYPE_REMOVE          = 6;

        final public const int TYPE_DISTINCT        = 9;

        final public const int TYPE_COUNT           = 11;

        final public const int HINT_REFRESH = 1;

        // 2 was used for HINT_SLAVE_OKAY, which was removed in 2.0
        final public const int HINT_READ_PREFERENCE = 3;

        final public const int HINT_READ_ONLY       = 5;

        public object|iterable|int|null $resultToReturn = null;

        public static ?SpaceContext $testsContext = null;

        public static ?ObjectManager $testsObjecttManager = null;

        /**
         * @param DocumentManager|null $dm
         * @param ClassMetadata|null $class
         * @param Collection|null $collection
         */
        public function __construct(
            private mixed $dm = null,
            private mixed $class = null,
            private mixed $collection = null,
            private array $query = [],
            private array $options = [],
            private bool $hydrate = true,
            private bool $refresh = false,
            private array $primers = [],
            private bool $readOnly = false,
            private bool $rewindable = true,
        ) {
        }

        /**
         * @var iterable|int
         */
        public function execute(): mixed
        {
            $type = ($this->query['type'] ?? self::TYPE_FIND);
            if (self::TYPE_REMOVE === $type) {
                $className = $this->class->getName();
                $objects = self::$testsContext->findObjectsBycriteria(
                    className: $className,
                    criteria: $this->query['query']['criteria'] ?? []
                );

                foreach ($objects as $obj) {
                    self::$testsContext->removeObject($obj);
                }
            }

            if (self::TYPE_COUNT === $type) {
                return count($this->resultToReturn);
            }

            return $this->resultToReturn;
        }

        /**
         * @return array|object|null
         */
        public function getSingleResult(): mixed
        {
            if (empty($this->resultToReturn)) {
                return null;
            }

            if (is_iterable($this->resultToReturn)) {
                foreach ($this->resultToReturn as $row) {
                    return $row;
                }

                return null;
            }

            return $this->resultToReturn;
        }

        public function setHydrate(bool $hydrate): void
        {
        }

        public function getIterator(): Iterator
        {
            // TODO: Implement getIterator() method.
        }
    }
}
