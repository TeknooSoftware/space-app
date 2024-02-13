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
use Teknoo\East\Foundation\Normalizer\EastNormalizerInterface;
use Teknoo\East\Foundation\Normalizer\Object\GroupsTrait;
use Teknoo\East\Foundation\Normalizer\Object\NormalizableInterface;
use Teknoo\East\Paas\Object\Project;
use Teknoo\East\Paas\Object\Traits\ExportConfigurationsTrait;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class PersistedVariable implements
    IdentifiedObjectInterface,
    TimestampableInterface,
    ImmutableInterface,
    NormalizableInterface
{
    use ObjectTrait;
    use ImmutableTrait;
    use GroupsTrait;
    use ExportConfigurationsTrait;

    private Project $project;

    private string $name;

    private ?string $value = null;

    private string $environmentName;

    private bool $secret = false;

    /**
     * @var array<string, string[]>
     */
    private static array $exportConfigurations = [
        '@class' => ['default', 'crud_variables'],
        'id' => ['default', 'crud_variables'],
        'name' => ['crud_variables'],
        'value' => ['crud_variables'],
        'environmentName' => ['crud_variables'],
        'secret' => ['crud_variables'],
    ];

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

    public function exportToMeData(EastNormalizerInterface $normalizer, array $context = []): NormalizableInterface
    {
        $value = null;
        if (!$this->isSecret()) {
            $value = $this->getValue();
        }

        $data = [
            '@class' => self::class,
            'id' => $this->getId(),
            'name' => $this->getName(),
            'value' => $value,
            'environmentName' => $this->getEnvironmentName(),
            'secret' => $this->isSecret(),
        ];

        $this->setGroupsConfiguration(self::$exportConfigurations);

        $normalizer->injectData(
            $this->filterExport(
                data: $data,
                groups: (array) ($context['groups'] ?? ['default']),
            )
        );

        return $this;
    }
}
