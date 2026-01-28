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

namespace Teknoo\Space\Tests\Behat\ODM;

use ArrayObject;
use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Doctrine\ODM\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\Persistence\ObjectManager;
use Teknoo\Space\Tests\Behat\SpaceContext;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * Disabled, not needed in test
 */
class MemoryRepository extends DocumentRepository
{
    public function __construct(
        private readonly string $className,
        private readonly ObjectManager $objectManager,
        private readonly SpaceContext $context,
    ) {
    }

    public function register(string $id, object $object): self
    {
        $this->objectManager->persist($object);

        return $this;
    }

    #[\Override]
    public function findOneBy(array $criteria, ?array $sort = null): ?object
    {
        if (isset($criteria['id'])) {
            return $this->context->findObjectById($this->className, $criteria['id']);
        }

        foreach ($this->context->findObjectsByCriteria($this->className, $criteria) as $object) {
            return $object;
        }

        return null;
    }

    #[\Override]
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        return $this->context->findObjectsByCriteria($this->className, $criteria);
    }

    #[\Override]
    public function getClassName(): string
    {
        return $this->className;
    }

    #[\Override]
    public function createQueryBuilder(): QueryBuilder
    {
        return new class ($this->context, $this->className) extends QueryBuilder {
            private array $criteria = [];

            private ?int $limit = null;

            public function __construct(
                private readonly SpaceContext $context,
                private readonly string $className,
            ) {
            }

            public function equals($value): QueryBuilder
            {
                $this->criteria = $value;

                return $this;
            }

            public function limit(int $limit): QueryBuilder
            {
                $this->limit = $limit;

                return $this;
            }

            public function prime($primer = true): QueryBuilder
            {
                return $this;
            }

            public function getQuery(array $options = []): Query
            {
                $query = new Query(
                    query: [
                        'type' => $this->getType(),
                    ],
                );

                $query->resultToReturn = new ArrayObject(
                    $this->context->findObjectsByCriteria(
                        $this->className,
                        $this->criteria,
                        $this->limit,
                    )
                );

                return $query;
            }
        };
    }
}
