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

namespace Teknoo\Space\Object\DTO;

use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Paas\Contracts\Security\SensitiveContentInterface;

use function bin2hex;
use function json_decode;
use function json_encode;
use function random_bytes;

use const JSON_THROW_ON_ERROR;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class NewJob implements ObjectInterface, SensitiveContentInterface
{
    private ?string $encryptionAlgorithm = null;

    private ?string $encryptedVariables = null;

    /**
     * @param JobVar[] $variables
     * @param array<string, string> $storageProvisionerPerCluster
     */
    public function __construct(
        public string $newJobId = '',
        public array $variables = [],
        public ?string $projectId = null,
        public ?string $accountId = null,
        public ?string $envName = null,
        public array $storageProvisionerPerCluster = [],
    ) {
        if (empty($this->newJobId)) {
            $this->newJobId = bin2hex(random_bytes(24));
        }
    }

    /*
     * To remove all occurences of persisted object or doctrine proxies in a serialized representation
     */
    public function export(bool $asArray = false): self
    {
        $that = clone $this;
        $that->variables = [];
        foreach ($this->variables as $variable) {
            $that->variables[] = $variable->export();
        }

        return $that;
    }

    public function getMessage(): string
    {
        if (null !== $this->encryptedVariables) {
            return $this->encryptedVariables;
        }

        return json_encode(
            value: $this->variables,
            flags: JSON_THROW_ON_ERROR
        );
    }

    public function getContent(): string
    {
        return $this->getMessage();
    }

    public function getEncryptionAlgorithm(): ?string
    {
        return $this->encryptionAlgorithm;
    }

    public function cloneWith(string $content, ?string $encryptionAlgorithm): SensitiveContentInterface
    {
        $that = clone $this;
        $that->encryptionAlgorithm = $encryptionAlgorithm;

        if (null !== $encryptionAlgorithm) {
            $that->encryptedVariables = $content;
            $that->variables = [];
        } else {
            $that->encryptedVariables = null;
            $that->variables = [];

            /**
             * @var array<int, array{id: ?string, name: string, value: ?string, persisted: bool, secret: bool}> $raw
             */
            $raw = (array) json_decode(
                json: $content,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );

            foreach ($raw as &$var) {
                $that->variables[] = new JobVar(...$var);
            }
        }

        return $that;
    }
}
