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
 * @link        https://teknoo.software/applications/space Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Behat\ODM;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Teknoo\Space\Tests\Behat\SpaceContext;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * Disabled, not needed in test
 */
class MemoryObjectManager implements ObjectManager
{
    /**
     * @var callable
     */
    private $getRepository;

    public function __construct(
        callable $getRepository,
        private SpaceContext $context,
    ) {
        $this->getRepository = $getRepository;
    }

    public function find($className, $id): ?object
    {
        return $this->context->findObjectById($className, $id);
    }

    public function persist($object): void
    {
        $this->context->persistObject($object);
    }

    public function remove($object): void
    {
        $this->context->removeObject($object);
    }

    public function clear(): void
    {
    }

    public function detach($object): void
    {
    }

    public function refresh($object): void
    {
    }

    public function flush(): void
    {
    }

    public function getRepository($className): ObjectRepository
    {
        return ($this->getRepository)($className);
    }

    public function getClassMetadata($className): ClassMetadata
    {
    }

    public function getMetadataFactory(): ClassMetadataFactory
    {
    }

    public function initializeObject($obj): void
    {
    }

    public function contains($object): bool
    {
    }

    public function isUninitializedObject(mixed $value): bool
    {
    }
}
