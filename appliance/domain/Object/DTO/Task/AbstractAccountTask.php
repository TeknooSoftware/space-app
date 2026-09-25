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

namespace Teknoo\Space\Object\DTO\Task;

use SensitiveParameter;
use Teknoo\East\Paas\Contracts\Security\SensitiveContentInterface;
use Teknoo\Space\Contracts\DTO\NewTaskInterface;

use function bin2hex;
use function random_bytes;

/**
 * Base of the account provisioning tasks (registry install/reinstall, environment install/reinstall, quota
 * refresh). The HTTP request only builds one of these and hands it to the `new_task` worker through
 * `CallNewTask`; `NewTaskHandler` resolves the matching `AccountProvisioningTask` plan thanks to the
 * `Teknoo\Space\Service\NewTaskRecipeRegistry` and executes it directly.
 *
 * The task only carries identifiers: the worker reloads the account, its history, its clusters, environments
 * and registry itself. `envName` / `clusterName` are exported to the workplan only when set, so the
 * `ProvisioningPlanBowl` falls back to the account's registry cluster for account-level tasks.
 *
 * Nothing here is sensitive, but the full `SensitiveContentInterface` round trip is honoured (payload and
 * algorithm are kept as-is), because an agent configured with encryption refuses unencrypted messages.
 *
 * It is deliberately NOT an `ObjectInterface`: in the worker, steps typed on `ObjectInterface` must keep
 * resolving to the loaded account.
 *
 * The constructor is shared by all tasks (it is invoked from a class-string by `PrepareAccountTask`).
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractAccountTask implements NewTaskInterface
{
    private ?string $encryptionAlgorithm = null;

    private ?string $encryptedPayload = null;

    /**
     * @param array<object> $variables
     */
    public function __construct(
        public string $taskId = '',
        public ?string $accountId = null,
        public ?string $envName = null,
        public ?string $clusterName = null,
        public array $variables = [],
    ) {
        if (empty($this->taskId)) {
            $this->taskId = bin2hex(random_bytes(24));
        }
    }

    /*
     * To remove all occurences of persisted object or doctrine proxies in a serialized representation
     */
    public function export(): NewTaskInterface
    {
        $that = clone $this;
        $that->variables = [];

        return $that;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $array = [
            'taskId' => $this->taskId,
            'accountId' => $this->accountId,
            'extra' => ['task_id' => $this->taskId],
        ];

        if (null !== $this->envName) {
            $array['envName'] = $this->envName;
        }

        if (null !== $this->clusterName) {
            $array['clusterName'] = $this->clusterName;
        }

        return $array;
    }

    public function getMessage(): string
    {
        return $this->encryptedPayload ?? '{}';
    }

    public function getContent(): string
    {
        return $this->getMessage();
    }

    public function getEncryptionAlgorithm(): ?string
    {
        return $this->encryptionAlgorithm;
    }

    public function cloneWith(
        #[SensitiveParameter]
        string $content,
        ?string $encryptionAlgorithm,
    ): SensitiveContentInterface {
        $that = clone $this;
        $that->encryptionAlgorithm = $encryptionAlgorithm;

        if (null !== $encryptionAlgorithm) {
            $that->encryptedPayload = $content;
        } else {
            $that->encryptedPayload = null;
        }

        return $that;
    }
}
