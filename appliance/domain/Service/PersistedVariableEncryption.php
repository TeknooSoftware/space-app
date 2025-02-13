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
 * @link        https://teknoo.software/applications/space Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Service;

use RuntimeException;
use Teknoo\East\Paas\Contracts\Security\EncryptionInterface;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Contracts\Object\EncryptableVariableInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class PersistedVariableEncryption
{
    public function __construct(
        private ?EncryptionInterface $service,
        private bool $isAgent,
    ) {
    }

    public function setAgentMode(bool $isAgent): PersistedVariableEncryption
    {
        $this->isAgent = $isAgent;

        return $this;
    }

    /**
     * @param PromiseInterface<EncryptableVariableInterface, mixed> $promise
     */
    public function encrypt(EncryptableVariableInterface $encryptableVariable, PromiseInterface $promise): self
    {
        if (!$this->service || $encryptableVariable->isEncrypted()) {
            $promise->success($encryptableVariable);

            return $this;
        }

        if ($this->isAgent) {
            $promise->fail(
                new RuntimeException('PersistedVariableEncryption::encrypt() is not available in agent mode')
            );
        }

        $this->service->encrypt(
            data: $encryptableVariable,
            promise: $promise,
            returnBase64: true,
        );

        return $this;
    }

    /**
     * @param PromiseInterface<EncryptableVariableInterface, mixed> $promise
     */
    public function decrypt(EncryptableVariableInterface $encryptableVariable, PromiseInterface $promise): self
    {
        if (!$this->service || $encryptableVariable->mustEncrypt()) {
            $promise->success($encryptableVariable);

            return $this;
        }

        if (!$this->isAgent) {
            return $this;
        }

        $this->service->decrypt(
            data: $encryptableVariable,
            promise: $promise,
            isBase64: true,
        );

        return $this;
    }
}
