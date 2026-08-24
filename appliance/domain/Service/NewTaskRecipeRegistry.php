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

namespace Teknoo\Space\Service;

use DomainException;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Space\Contracts\DTO\NewTaskInterface;

use function is_a;

/**
 * To find the recipe/plan able to execute a task, from the task's class name.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class NewTaskRecipeRegistry
{
    /**
     * @var array<class-string<NewTaskInterface>, BaseRecipeInterface>
     */
    private array $recipes = [];

    /**
     * @param class-string<NewTaskInterface> $taskClass
     */
    public function register(string $taskClass, BaseRecipeInterface $recipe): NewTaskRecipeRegistry
    {
        if (!is_a($taskClass, NewTaskInterface::class, true)) {
            throw new DomainException("Error, the task class {$taskClass} is not a valid task");
        };

        $this->recipes[$taskClass] = $recipe;

        return $this;
    }

    /**
     * @param class-string<NewTaskInterface> $taskClass
     */
    public function get(string $taskClass): BaseRecipeInterface
    {
        return $this->recipes[$taskClass]
            ?? throw new DomainException("Error, no recipe is registered for the task {$taskClass}");
    }
}
