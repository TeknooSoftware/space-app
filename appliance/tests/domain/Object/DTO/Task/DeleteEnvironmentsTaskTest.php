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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Space\Object\DTO\Task\AbstractAccountTask;
use Teknoo\Space\Object\DTO\Task\DeleteEnvironmentsTask;

/**
 * Class DeleteEnvironmentsTaskTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(DeleteEnvironmentsTask::class)]
#[CoversClass(AbstractAccountTask::class)]
class DeleteEnvironmentsTaskTest extends TestCase
{
    use AccountTaskTestTrait;

    private const array ENVIRONMENTS = [
        ['envName' => 'dev', 'clusterName' => 'cluster', 'namespace' => 'space-client-foo-dev'],
        ['envName' => 'prod', 'clusterName' => 'other', 'namespace' => 'space-client-foo-prod'],
    ];

    protected function getTaskClass(): string
    {
        return DeleteEnvironmentsTask::class;
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
                'environmentsToDelete' => [],
            ],
            $this->buildTask()->toArray(),
        );
    }

    public function testToArrayOmitsTheEnvironmentWhenNotSet(): void
    {
        $this->assertSame(
            [
                'taskId' => 'task-id',
                'accountId' => 'account-id',
                'extra' => ['task_id' => 'task-id'],
                'environmentsToDelete' => [],
            ],
            (new DeleteEnvironmentsTask(taskId: 'task-id', accountId: 'account-id'))->toArray(),
        );
    }

    public function testTheEnvironmentsListIsExportedAndSurvivesTheEncryptionRoundTrip(): void
    {
        $task = new DeleteEnvironmentsTask(
            taskId: 'task-id',
            accountId: 'account-id',
            environments: self::ENVIRONMENTS,
        );

        $this->assertSame(self::ENVIRONMENTS, $task->environments);
        $this->assertSame(self::ENVIRONMENTS, $task->toArray()['environmentsToDelete']);
        $exported = $task->export();
        $this->assertInstanceOf(DeleteEnvironmentsTask::class, $exported);
        $this->assertSame(self::ENVIRONMENTS, $exported->environments);

        $decrypted = $task->cloneWith('encrypted-payload', 'rsa')->cloneWith('{}', null);
        $this->assertInstanceOf(DeleteEnvironmentsTask::class, $decrypted);
        $this->assertSame(self::ENVIRONMENTS, $decrypted->environments);
    }
}
