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

namespace Teknoo\Space\Contracts\DTO;

use Teknoo\East\Paas\Contracts\Security\SensitiveContentInterface;
use Teknoo\Space\Object\DTO\JobVar;

/**
 * To define a task to execute asynchronously in a worker, instead of the web server.
 * Each implementation is dedicated to an operation, and is mapped to a recipe/plan thanks to the
 * `Teknoo\Space\Service\NewTaskRecipeRegistry`.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
interface NewTaskInterface extends SensitiveContentInterface
{
    public string $taskId { get; }

    public ?string $accountId { get; }

    /**
     * @var array<object>
     */
    public array $variables { get; }

    /*
     * To remove all occurences of persisted object or doctrine proxies in a serialized representation
     */
    public function export(): NewTaskInterface;

    public function getMessage(): string;

    /**
     * To convert this task to the initial workplan of its recipe.
     *
     * @return array<string, string|int|bool|array<int|string, string|int|bool>>
     */
    public function toArray(): array;
}
