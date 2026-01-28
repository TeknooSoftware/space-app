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

namespace Teknoo\Space\Tests\Behat\Traits;

use Behat\Step\Given;
use Doctrine\ODM\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\Persistence\ObjectManager;
use ReflectionObject;
use RuntimeException;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Doctrine\Object\Media as MediaODM;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Account;
use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Job;
use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Project;
use Teknoo\East\Paas\Object\Cluster;
use Teknoo\Space\Object\Persisted\AccountCluster;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Object\Persisted\AccountData;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;
use Teknoo\Space\Object\Persisted\AccountRegistry;
use Teknoo\Space\Object\Persisted\ProjectPersistedVariable;
use Teknoo\Space\Object\Persisted\ProjectMetadata;
use Teknoo\Space\Object\Persisted\UserData;
use Teknoo\Space\Tests\Behat\ODM\GridFSMemoryRepository;
use Teknoo\Space\Tests\Behat\ODM\MemoryObjectManager;
use Teknoo\Space\Tests\Behat\ODM\MemoryRepository;

use function bin2hex;
use function count;
use function current;
use function explode;
use function get_parent_class;
use function hash;
use function in_array;
use function is_array;
use function is_iterable;
use function key;
use function method_exists;
use function spl_object_hash;
use function str_contains;
use function random_bytes;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
trait PersistenceOperationTrait
{
    public function findObjectById(string $className, mixed $id): ?object
    {
        foreach ($this->objects[$className] ?? [] as $object) {
            if (!$object instanceof IdentifiedObjectInterface) {
                continue;
            }

            if ($object->getId() == $id) {
                return $object;
            }
        }

        return null;
    }

    private function testValue(mixed $expected, mixed $value): bool
    {
        if (is_array($value)) {
            return match (key($value)) {
                '$in' => in_array($expected, current($value)),
                '$nin' => !in_array($expected, current($value)),
                default => $expected === $value,
            };
        }

        return $expected === $value;
    }

    private function testACriteria(string $criteria, mixed &$value, ReflectionObject $roInstance, object $object): bool
    {
        $checkByGetter = function (string $method, mixed $value) use ($roInstance, $object): bool {
            if (!$roInstance->hasMethod($method)) {
                return false;
            }

            return $this->testValue($object->{$method}(), $value);
        };

        if ('$or' === $criteria) {
            $valid = false;
            foreach ($value as $subCriterias) {
                $valid = $this->testListOfCriteria($subCriterias, $roInstance, $object);

                if ($valid) {
                    break;
                }
            }

            return $valid;
        }

        if ('$and' === $criteria) {
            $valid = true;
            foreach ($value as $subCriterias) {
                $valid = $valid && $this->testListOfCriteria($subCriterias, $roInstance, $object);

                if (!$valid) {
                    break;
                }
            }

            return $valid;
        }

        if (str_contains($criteria, '.$')) {
            [$property, $attr] = explode('.$', $criteria);

            if ($roInstance->hasProperty($property)) {
                $rp = $roInstance->getProperty($property);

                $subObjectsList = $rp->getValue($object);
                if (!is_iterable($subObjectsList)) {
                    $subObjectsList = [$subObjectsList];
                }

                foreach ($subObjectsList as &$subObject) {
                    $sro = new ReflectionObject($subObject);
                    if ($this->testACriteria($attr, $value, $sro, $subObject)) {
                        return true;
                    }
                }

                return false;
            }
        }

        if ($roInstance->hasProperty($criteria)) {
            $rp = $roInstance->getProperty($criteria);

            if ($this->testValue($rp->getValue($object), $value)) {
                return true;
            }
        }

        if ($checkByGetter("get{$criteria}", $value)) {
            return true;
        }

        if ($checkByGetter("is{$criteria}", $value)) {
            return true;
        }

        if ($checkByGetter("has{$criteria}", $value)) {
            return true;
        }

        return false;
    }

    private function testListOfCriteria(array $criteria, ReflectionObject $roInstance, object $object): bool
    {
        foreach ($criteria as $name => &$value) {
            if (!$this->testACriteria($name, $value, $roInstance, $object)) {
                return false;
            }
        }

        return true;
    }

    public function findObjectsByCriteria(
        string $className,
        array $criteria,
        ?int $limit = null,
    ): iterable {
        $final = [];
        do {
            if (empty($this->objects[$className])) {
                continue;
            }

            $final = [];
            foreach ($this->objects[$className] as $object) {
                $roInstance = new ReflectionObject($object);

                if ($this->testListOfCriteria($criteria, $roInstance, $object)) {
                    $final[] = $object;
                }

                if (null !== $limit && count($final) >= $limit) {
                    break;
                }
            }
        } while (empty($final) && !empty($className = get_parent_class($className)));

        return $final;
    }

    public function listObjects(string $className): array
    {
        return $this->objects[$className] ?? [];
    }

    public function getObjectUniqueId(object $object): string
    {
        if ($object instanceof IdentifiedObjectInterface) {
            return 'IdentifiedObjectInterface:' . $object->getId();
        }

        return spl_object_hash($object);
    }

    public function getListOfPersistedObjects(string $class): array
    {
        return $this->objects[$class];
    }

    public function persistObject(object $object): void
    {
        if ($object instanceof IdentifiedObjectInterface) {
            if (
                empty($object->getId())
                && method_exists($object, 'setId')
            ) {
                $object->setId($this->generateId());
            }
        }

        $this->objects[$object::class][$this->getObjectUniqueId($object)] = $object;
    }

    public function removeObject(object $object): void
    {
        $oId = $this->getObjectUniqueId($object);
        if (isset($this->objects[$object::class][$oId])) {
            $this->removedObjects[$object::class][$oId] = $this->objects[$object::class][$oId];
            unset($this->objects[$object::class][$oId]);
        }
    }

    public function buildObjectManager(): ObjectManager
    {
        $this->objectManager ??= new MemoryObjectManager($this->getRepository(...), $this);

        Query::$testsObjecttManager = $this->objectManager;
        return $this->objectManager;
    }

    public function getRepository(string $className): DocumentRepository
    {
        if (!isset($this->repositories[$className])) {
            throw new RuntimeException("Missing $className");
        }

        return $this->repositories[$className];
    }

    public function buildRepository(string $className): DocumentRepository
    {
        return $this->repositories[$className] = new MemoryRepository(
            $className,
            $this->buildObjectManager(),
            $this,
        );
    }

    public function buildGridFSRepository(string $className): DocumentRepository
    {
        return $this->repositories[$className] = new GridFSMemoryRepository(
            $className,
            $this->buildObjectManager(),
            $this,
        );
    }

    #[Given('A memory document database')]
    public function aMemoryDocumentDatabase(): void
    {
        $this->sfContainer->set(ObjectManager::class, $this->buildObjectManager());

        $this->buildRepository(User::class);
        $this->buildGridFSRepository(MediaODM::class);

        $this->buildRepository(Account::class);
        $this->buildRepository(Cluster::class);
        $this->buildRepository(Job::class);
        $this->buildRepository(Project::class);

        $this->buildRepository(AccountEnvironment::class);
        $this->buildRepository(AccountRegistry::class);
        $this->buildRepository(AccountData::class);
        $this->buildRepository(AccountHistory::class);
        $this->buildRepository(AccountCluster::class);
        $this->buildRepository(AccountPersistedVariable::class);
        $this->buildRepository(ProjectPersistedVariable::class);
        $this->buildRepository(ProjectMetadata::class);
        $this->buildRepository(UserData::class);
    }

    public function register(object $object): void
    {
        $this->workMemory[$object::class] = $object;
    }

    public function persistAndRegister(object $object): void
    {
        $this->persistObject($object);
        $this->register($object);
    }

    /**
     * @param class-string $classname
     */
    public function recall(string $classname): ?object
    {
        return $this->workMemory[$classname] ?? null;
    }

    public function generateId(): string
    {
        return hash(
            algo: 'sha256',
            data: bin2hex(random_bytes(23))
        );
    }
}
