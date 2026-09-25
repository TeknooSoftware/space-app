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

namespace Teknoo\Space\Tests\Unit\Object\DTO\Task;

use stdClass;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Paas\Contracts\Security\SensitiveContentInterface;
use Teknoo\Space\Contracts\DTO\NewTaskInterface;
use Teknoo\Space\Object\DTO\Task\AbstractAccountTask;

use function strlen;

/**
 * Shared contract tests of the account provisioning tasks: every concrete task must behave the same, only
 * the class differs.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 */
trait AccountTaskTestTrait
{
    /**
     * @return class-string<AbstractAccountTask>
     */
    abstract protected function getTaskClass(): string;

    private function buildTask(): AbstractAccountTask
    {
        $class = $this->getTaskClass();

        return new $class(
            taskId: 'task-id',
            accountId: 'account-id',
            envName: 'prod',
            clusterName: 'cluster',
        );
    }

    public function testItIsATaskButNotAPersistedObject(): void
    {
        $task = $this->buildTask();

        $this->assertInstanceOf(NewTaskInterface::class, $task);
        $this->assertInstanceOf(AbstractAccountTask::class, $task);
        $this->assertNotInstanceOf(ObjectInterface::class, $task);
    }

    public function testConstructWithAllProperties(): void
    {
        $task = $this->buildTask();

        $this->assertSame('task-id', $task->taskId);
        $this->assertSame('account-id', $task->accountId);
        $this->assertSame('prod', $task->envName);
        $this->assertSame('cluster', $task->clusterName);
        $this->assertSame([], $task->variables);
    }

    public function testConstructWithDefaults(): void
    {
        $class = $this->getTaskClass();
        $task = new $class();

        $this->assertNull($task->accountId);
        $this->assertNull($task->envName);
        $this->assertNull($task->clusterName);
        $this->assertSame([], $task->variables);
    }

    public function testItGeneratesATaskIdWhenNoneIsGiven(): void
    {
        $class = $this->getTaskClass();
        $first = new $class();
        $second = new $class();

        $this->assertSame(48, strlen($first->taskId));
        $this->assertNotSame($first->taskId, $second->taskId);
    }

    public function testExportDropsVariablesAndKeepsTheOriginalUntouched(): void
    {
        $class = $this->getTaskClass();
        $task = new $class(taskId: 'task-id', accountId: 'account-id', variables: [new stdClass()]);

        $exported = $task->export();

        $this->assertInstanceOf($class, $exported);
        $this->assertSame([], $exported->variables);
        $this->assertCount(1, $task->variables);
        $this->assertSame('task-id', $exported->taskId);
        $this->assertSame('account-id', $exported->accountId);
    }

    public function testToArrayFeedsTheWorkerWorkplanWithTheEnvironment(): void
    {
        $this->assertSame(
            [
                'taskId' => 'task-id',
                'accountId' => 'account-id',
                'extra' => ['task_id' => 'task-id'],
                'envName' => 'prod',
                'clusterName' => 'cluster',
            ],
            $this->buildTask()->toArray(),
        );
    }

    public function testToArrayOmitsTheEnvironmentWhenNotSet(): void
    {
        $class = $this->getTaskClass();

        $this->assertSame(
            [
                'taskId' => 'task-id',
                'accountId' => 'account-id',
                'extra' => ['task_id' => 'task-id'],
            ],
            (new $class(taskId: 'task-id', accountId: 'account-id'))->toArray(),
        );
    }

    public function testThePayloadIsEmptyAndNotEncryptedByDefault(): void
    {
        $task = $this->buildTask();

        $this->assertSame('{}', $task->getMessage());
        $this->assertSame('{}', $task->getContent());
        $this->assertNull($task->getEncryptionAlgorithm());
    }

    public function testCloneWithAnAlgorithmKeepsTheEncryptedPayload(): void
    {
        $task = $this->buildTask();

        $encrypted = $task->cloneWith('encrypted-payload', 'rsa');

        $this->assertInstanceOf(SensitiveContentInterface::class, $encrypted);
        $this->assertNotSame($task, $encrypted);
        $this->assertSame('rsa', $encrypted->getEncryptionAlgorithm());
        $this->assertSame('encrypted-payload', $encrypted->getMessage());
        $this->assertSame('encrypted-payload', $encrypted->getContent());
        $this->assertSame('task-id', $encrypted->taskId);
        $this->assertSame('account-id', $encrypted->accountId);
        $this->assertNull($task->getEncryptionAlgorithm());
    }

    public function testTheEncryptedThenDecryptedRoundTrip(): void
    {
        $task = $this->buildTask();

        $decrypted = $task->cloneWith('encrypted-payload', 'rsa')->cloneWith('{}', null);

        $this->assertInstanceOf($this->getTaskClass(), $decrypted);
        $this->assertNull($decrypted->getEncryptionAlgorithm());
        $this->assertSame('{}', $decrypted->getMessage());
        $this->assertSame($task->toArray(), $decrypted->toArray());
    }
}
