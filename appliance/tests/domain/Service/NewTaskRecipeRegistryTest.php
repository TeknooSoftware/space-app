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

namespace Teknoo\Space\Tests\Unit\Service;

use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Space\Object\DTO\NewJob;
use Teknoo\Space\Service\NewTaskRecipeRegistry;

/**
 * Class NewTaskRecipeRegistryTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(NewTaskRecipeRegistry::class)]
class NewTaskRecipeRegistryTest extends TestCase
{
    private NewTaskRecipeRegistry $registry;

    private BaseRecipeInterface&Stub $recipe;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = new NewTaskRecipeRegistry();
        $this->recipe = $this->createStub(BaseRecipeInterface::class);
    }

    public function testRegisterReturnsTheRegistry(): void
    {
        $this->assertSame(
            $this->registry,
            $this->registry->register(NewJob::class, $this->recipe),
        );
    }

    public function testGetReturnsTheRegisteredRecipe(): void
    {
        $this->registry->register(NewJob::class, $this->recipe);

        $this->assertSame($this->recipe, $this->registry->get(NewJob::class));
    }

    public function testRegisterOverwritesAPreviousRecipe(): void
    {
        $otherRecipe = $this->createStub(BaseRecipeInterface::class);

        $this->registry
            ->register(NewJob::class, $this->recipe)
            ->register(NewJob::class, $otherRecipe);

        $this->assertSame($otherRecipe, $this->registry->get(NewJob::class));
    }

    public function testGetThrowsADomainExceptionForAnUnknownTask(): void
    {
        $this->expectException(DomainException::class);

        $this->registry->get(NewJob::class);
    }
}
