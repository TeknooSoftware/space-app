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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Behat\ODM;

use Doctrine\Persistence\ObjectManager;
use Teknoo\Space\Tests\Behat\TestsContext;

class MemoryObjectManager implements ObjectManager
{
    /**
     * @var callable
     */
    private $getRepository;

    public function __construct(
        callable $getRepository,
        private TestsContext $context,
    ){
        $this->getRepository = $getRepository;
    }

    public function find($className, $id): ?object
    {
        return $this->context->findObjectById($className, $id);
    }

    public function persist($object): void {
        $this->context->persistObject($object);
    }

    public function remove($object): void {
        $this->context->removeObject($object);
    }

    public function clear(): void {
    }

    public function detach($object) {}

    public function refresh($object) {}

    public function flush() {}

    public function getRepository($className) {
        return ($this->getRepository)($className);
    }

    public function getClassMetadata($className) {}

    public function getMetadataFactory() {}

    public function initializeObject($obj) {}

    public function contains($object) {}
}