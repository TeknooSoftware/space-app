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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Kubernetes\Traits;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;
use Teknoo\Kubernetes\Model\Model;
use Teknoo\Kubernetes\Model\Secret;
use Teknoo\Kubernetes\Repository\Repository;
use Teknoo\Kubernetes\Repository\SecretRepository;
use Teknoo\Space\Infrastructures\Kubernetes\Exception\KubernetesErrorException;
use Teknoo\Space\Infrastructures\Kubernetes\Traits\InsertModelTrait;

/**
 * Class InsertModelTraitTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversTrait(InsertModelTrait::class)]
class InsertModelTraitTest extends TestCase
{
    private function createInserter(): object
    {
        return new class {
            use InsertModelTrait;

            /**
             * @param Repository<Model> $repository
             */
            public function insert(Repository $repository, Model $model, bool $updateIfExist = false): void
            {
                $this->insertModel($repository, $model, $updateIfExist);
            }
        };
    }

    public function testCreateWhenTheModelDoesNotExist(): void
    {
        $repository = $this->createMock(SecretRepository::class);
        $repository->expects($this->once())
            ->method('exists')
            ->with('foo')
            ->willReturn(false);
        $repository->expects($this->once())
            ->method('create')
            ->willReturn(['status' => 'Success']);
        $repository->expects($this->never())
            ->method('update');

        $this->createInserter()->insert($repository, new Secret(['metadata' => ['name' => 'foo']]));
    }

    public function testUpdateWhenTheModelExists(): void
    {
        $repository = $this->createMock(SecretRepository::class);
        $repository->expects($this->once())
            ->method('exists')
            ->with('foo')
            ->willReturn(true);
        $repository->expects($this->never())
            ->method('create');
        $repository->expects($this->once())
            ->method('update')
            ->willReturn([]);

        $this->createInserter()->insert($repository, new Secret(['metadata' => ['name' => 'foo']]), true);
    }

    public function testNothingWhenTheModelExistsAndUpdateIsDisabled(): void
    {
        $repository = $this->createMock(SecretRepository::class);
        $repository->expects($this->once())
            ->method('exists')
            ->willReturn(true);
        $repository->expects($this->never())
            ->method('create');
        $repository->expects($this->never())
            ->method('update');

        $this->createInserter()->insert($repository, new Secret(['metadata' => ['name' => 'foo']]));
    }

    public function testFailureWithMessage(): void
    {
        $repository = $this->createStub(SecretRepository::class);
        $repository->method('exists')->willReturn(false);
        $repository->method('create')->willReturn(['status' => 'Failure', 'message' => 'boom']);

        $this->expectException(KubernetesErrorException::class);
        $this->expectExceptionMessage('boom');

        $this->createInserter()->insert($repository, new Secret(['metadata' => ['name' => 'foo']]));
    }

    public function testFailureWithoutMessage(): void
    {
        $repository = $this->createStub(SecretRepository::class);
        $repository->method('exists')->willReturn(false);
        $repository->method('create')->willReturn(['status' => 'Failure']);

        $this->expectException(KubernetesErrorException::class);
        $this->expectExceptionMessage('Error in kubernetes request');

        $this->createInserter()->insert($repository, new Secret(['metadata' => ['name' => 'foo']]));
    }
}
