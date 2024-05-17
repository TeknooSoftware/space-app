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

namespace Teknoo\Space\Object\DTO;

use JsonSerializable;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Paas\Contracts\Security\SensitiveContentInterface;
use Teknoo\Space\Contracts\Object\EncryptableVariableInterface;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;
use Teknoo\Space\Object\Persisted\ProjectPersistedVariable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class JobVar implements ObjectInterface, JsonSerializable, EncryptableVariableInterface
{
    public function __construct(
        private ?string $id = null,
        public string $name = '',
        public ?string $value = '',
        public bool $persisted = false,
        public bool $secret = false,
        public ?bool $wasSecret = null,
        public ?string $encryptionAlgorithm = null,
        public ?bool $canPersist = false,
        private ProjectPersistedVariable|AccountPersistedVariable|null $persistedVar = null,
    ) {
        if (null === $this->wasSecret) {
            $this->wasSecret = $this->secret;
        }
    }

    public function getId(): ?string
    {
        return $this->persistedVar?->getId() ?? $this->id;
    }

    /*
     * To remove all occurences of persisted object or doctrine proxies in a serialized representation
     */
    public function export(): self
    {
        $that = clone $this;
        $that->persistedVar = null;
        $that->wasSecret = false;

        return $that;
    }

    public function isSecret(): bool
    {
        return $this->secret;
    }

    public function isEncrypted(): bool
    {
        return !empty($this->encryptionAlgorithm);
    }

    public function mustEncrypt(): bool
    {
        return false;
    }

    public function getContent(): string
    {
        return (string) $this->value;
    }

    public function getEncryptionAlgorithm(): ?string
    {
        return $this->encryptionAlgorithm;
    }

    public function cloneWith(string $content, ?string $encryptionAlgorithm): SensitiveContentInterface
    {
        $that = clone $this;
        $that->value = $content;
        $that->encryptionAlgorithm = $encryptionAlgorithm;

        return $that;
    }

    /**
     * @return array<string, string|bool|null>
     */
    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'value' => $this->value,
            'persisted' => $this->persisted,
            'secret' => $this->secret,
            'encryptionAlgorithm' => $this->encryptionAlgorithm,
            'canPersist' => $this->canPersist,
        ];
    }
}
