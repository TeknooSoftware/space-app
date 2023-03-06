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

namespace Teknoo\Space\Object\Persisted;

use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\TimestampableInterface;
use Teknoo\East\Common\Object\ObjectTrait;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class PersistedVariable implements IdentifiedObjectInterface, TimestampableInterface, ImmutableInterface
{
    use ObjectTrait;
    use ImmutableTrait;

    private Project $project;

    private string $name;

    private ?string $value = null;

    private string $environmentName;

    private bool $secret = false;

    public function __construct(
        Project $project,
        ?string $id,
        string $name,
        ?string $value,
        string $environmentName,
        bool $secret,
    ) {
        $this->uniqueConstructorCheck();

        $this->id = $id;
        $this->project = $project;
        $this->name = $name;
        $this->value = $value;
        $this->environmentName = $environmentName;
        $this->secret = $secret;
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function getEnvironmentName(): string
    {
        return $this->environmentName;
    }

    public function isSecret(): bool
    {
        return $this->secret;
    }
}
