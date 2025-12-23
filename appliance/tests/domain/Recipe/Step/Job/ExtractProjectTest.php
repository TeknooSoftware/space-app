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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Job;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Job;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Recipe\Step\Job\ExtractProject;

/**
 * Class ExtractProjectTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ExtractProject::class)]
class ExtractProjectTest extends TestCase
{
    private ExtractProject $extractProject;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();


        $this->extractProject = new ExtractProject();
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            ExtractProject::class,
            ($this->extractProject)(
                manager: $this->createStub(ManagerInterface::class),
                job: $this->createStub(Job::class),
            )
        );
    }

    public function testInvokeWithProjectExtraction(): void
    {
        $project = $this->createStub(Project::class);

        $job = $this->createMock(Job::class);
        $job->expects($this->once())
            ->method('getProject')
            ->willReturn($project);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) use ($project) {
                    return isset($workplan[Project::class])
                        && $workplan[Project::class] === $project;
                })
            );

        $result = ($this->extractProject)(
            manager: $manager,
            job: $job,
        );

        $this->assertInstanceOf(ExtractProject::class, $result);
    }
}
