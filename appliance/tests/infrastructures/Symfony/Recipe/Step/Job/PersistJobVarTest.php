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
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Job\PersistJobVar;
use Teknoo\Space\Object\DTO\JobVar;
use Teknoo\Space\Object\DTO\NewJob;
use Teknoo\Space\Object\DTO\SpaceProject;
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

    public function testInvoke(): void
    {
        $newJob = new NewJob(
            variables: [
                new JobVar('foo'),
            ],
        );
        $this->assertInstanceOf(
            PersistJobVar::class,
            ($this->persistJobVar)(
                $this->createMock(ManagerInterface::class),
                $newJob,
                new SpaceProject($this->createMock(Project::class)),
            )
        );
    }
}
