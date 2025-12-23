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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Doctrine\Repository\ODM;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Infrastructures\Doctrine\Repository\ODM\AccountDataRepository;
use Teknoo\Tests\East\Common\Doctrine\DBSource\ODM\RepositoryTestTrait;

/**
 * Class AccountDataRepositoryTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountDataRepository::class)]
class AccountDataRepositoryTest extends TestCase
{
    use RepositoryTestTrait;

    /**
     * @inheritDoc
     */
    public function buildRepository(): RepositoryInterface
    {
        return new AccountDataRepository($this->getDoctrineObjectRepositoryMock());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testFindBadId(): void
    {
        $this->expectException(\TypeError::class);
        $this->buildRepository()->find(new \stdClass(), $this->createStub(PromiseInterface::class));
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testFindBadPromise(): void
    {
        $this->expectException(\TypeError::class);
        $this->buildRepository()->find('abc', new \stdClass());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testFindAllBadPromise(): void
    {
        $this->expectException(\TypeError::class);
        $this->buildRepository()->findAll(new \stdClass());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testFindByBadCriteria(): void
    {
        $this->expectException(\TypeError::class);
        $this->buildRepository()->findBy(new \stdClass(), $this->createStub(PromiseInterface::class));
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testFindByBadPromise(): void
    {
        $this->expectException(\TypeError::class);
        $this->buildRepository()->findBy(['foo' => 'bar'], new \stdClass());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testFindByBadOrder(): void
    {
        $this->expectException(\TypeError::class);
        $this->buildRepository()->findBy(
            ['foo' => 'bar'],
            $this->createStub(PromiseInterface::class),
            new \stdClass()
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testFindOneByBadCriteria(): void
    {
        $this->expectException(\TypeError::class);
        $this->buildRepository()->findOneBy(new \stdClass(), $this->createStub(PromiseInterface::class));
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testFindOneByBadPromise(): void
    {
        $this->expectException(\TypeError::class);
        $this->buildRepository()->findOneBy(['foo' => 'bar'], new \stdClass());
    }
}
