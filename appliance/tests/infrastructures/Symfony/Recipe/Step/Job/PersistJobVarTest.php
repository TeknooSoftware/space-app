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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Recipe\Step\Job;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\DBSource\ManagerInterface as DbSourceManager;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Job\PersistJobVar;
use Teknoo\Space\Object\DTO\JobVar;
use Teknoo\Space\Object\DTO\NewJob;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\Persisted\ProjectPersistedVariable;
use Teknoo\Space\Writer\ProjectPersistedVariableWriter;

/**
 * Class PersistJobVarTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(PersistJobVar::class)]
class PersistJobVarTest extends TestCase
{
    private PersistJobVar $persistJobVar;

    private DbSourceManager&MockObject $manager;

    private ProjectPersistedVariableWriter&MockObject $writer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->createMock(DbSourceManager::class);
        $this->writer = $this->createMock(ProjectPersistedVariableWriter::class);
        $this->persistJobVar = new PersistJobVar(
            $this->writer,
            $this->manager,
        );
    }

    public function testInvokeWithoutPersistableVariable(): void
    {
        $this->manager->expects($this->once())->method('openBatch')->willReturnSelf();
        $this->manager->expects($this->once())->method('closeBatch')->willReturnSelf();
        $this->writer->expects($this->never())->method('save');

        $newJob = new NewJob(
            variables: [
                new JobVar('foo'),
            ],
        );
        $this->assertInstanceOf(
            PersistJobVar::class,
            ($this->persistJobVar)(
                $this->createStub(ManagerInterface::class),
                $newJob,
                new SpaceProject($this->createStub(Project::class)),
            )
        );
    }

    public function testInvokeWithPersistableVariables(): void
    {
        $this->manager->expects($this->once())->method('openBatch')->willReturnSelf();
        $this->manager->expects($this->once())->method('closeBatch')->willReturnSelf();

        $saved = [];
        $this->writer
            ->expects($this->exactly(2))
            ->method('save')
            ->willReturnCallback(
                function (ObjectInterface $object, PromiseInterface $promise) use (&$saved): WriterInterface {
                    $saved[] = $object;
                    $promise->success($object);

                    return $this->writer;
                }
            );

        $newJob = new NewJob(
            envName: 'prod',
            variables: [
                new JobVar(id: 'i1', name: 'n1', value: 'v1', persisted: true, secret: true, canPersist: true),
                new JobVar(
                    id: 'i2',
                    name: 'n2',
                    value: 'v2',
                    persisted: true,
                    secret: true,
                    encryptionAlgorithm: 'aes',
                    canPersist: true,
                ),
                new JobVar(id: 'i3', name: 'n3', value: 'v3', persisted: true, canPersist: false),
            ],
        );

        $this->assertInstanceOf(
            PersistJobVar::class,
            ($this->persistJobVar)(
                $this->createStub(ManagerInterface::class),
                $newJob,
                new SpaceProject($this->createStub(Project::class)),
            )
        );

        $this->assertCount(2, $saved);
        $this->assertContainsOnlyInstancesOf(ProjectPersistedVariable::class, $saved);
        $this->assertSame('n1', $saved[0]->getName());
        $this->assertSame('prod', $saved[0]->getEnvName());
    }
}
