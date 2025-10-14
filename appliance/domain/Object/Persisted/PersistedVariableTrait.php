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

namespace Teknoo\Space\Object\Persisted;

use LogicException;
use Teknoo\East\Common\Object\ObjectTrait;
use Teknoo\East\Foundation\Normalizer\EastNormalizerInterface;
use Teknoo\East\Foundation\Normalizer\Object\GroupsTrait;
use Teknoo\East\Foundation\Normalizer\Object\NormalizableInterface;
use Teknoo\East\Paas\Contracts\Security\SensitiveContentInterface;
use Teknoo\East\Paas\Object\Traits\ExportConfigurationsTrait;
use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Space\Contracts\Object\EncryptableVariableInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait PersistedVariableTrait
{
    use ObjectTrait;
    use ImmutableTrait;
    use GroupsTrait;
    use ExportConfigurationsTrait;

    private string $name;

    private ?string $value = null;

    private string $envName;

    private bool $secret = false;

    private ?string $encryptionAlgorithm = null;

    private bool $needEncryption = false;

    /**
     * @var array<string, string[]>
     */
    private static array $exportConfigurations = [
        '@class' => ['default', 'crud_variables'],
        'id' => ['default', 'crud_variables'],
        'name' => ['crud_variables'],
        'value' => ['crud_variables'],
        'envName' => ['crud_variables'],
        'secret' => ['crud_variables'],
        'encrypted' => ['crud_variables'],
    ];

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setEncryptedValue(string $algo, string $value): EncryptableVariableInterface
    {
        if (!$this->needEncryption) {
            throw new LogicException("This variable {$this->name} not need encryption");
        }

        $this->value = $value;
        $this->encryptionAlgorithm = $algo;
        $this->needEncryption = false;

        return $this;
    }

    public function setValue(string $value): EncryptableVariableInterface
    {
        $this->value = $value;

        return $this;
    }

    public function getEnvName(): string
    {
        return $this->envName;
    }

    public function isSecret(): bool
    {
        return $this->secret;
    }

    public function isEncrypted(): bool
    {
        return !empty($this->encryptionAlgorithm) && !$this->needEncryption;
    }

    public function mustEncrypt(): bool
    {
        return $this->needEncryption;
    }

    public function exportToMeData(EastNormalizerInterface $normalizer, array $context = []): NormalizableInterface
    {
        $value = null;
        if (!$this->isSecret()) {
            $value = $this->getValue();
        }

        if ($this->isEncrypted()) {
            $value = null;
        }

        $data = [
            '@class' => self::class,
            'id' => $this->getId(),
            'name' => $this->getName(),
            'value' => $value,
            'envName' => $this->getEnvName(),
            'secret' => $this->isSecret(),
            'encrypted' => $this->isEncrypted(),
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

    public function getContent(): string
    {
        return (string) $this->getValue();
    }

    public function getEncryptionAlgorithm(): ?string
    {
        return $this->encryptionAlgorithm;
    }

    public function cloneWith(string $content, ?string $encryptionAlgorithm): SensitiveContentInterface
    {
        if (null !== $encryptionAlgorithm) {
            $this->setEncryptedValue($encryptionAlgorithm, $content);

            return $this;
        }

        $that = clone $this;
        $that->setValue($content);

        return $that;
    }
}
