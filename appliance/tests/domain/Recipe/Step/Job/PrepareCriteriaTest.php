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
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Query\Expr\ObjectReference;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Recipe\Step\Job\PrepareCriteria;

/**
 * Class PrepareCriteriaTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(PrepareCriteria::class)]
class PrepareCriteriaTest extends TestCase
{
    private PrepareCriteria $prepareCriteria;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();


        $this->prepareCriteria = new PrepareCriteria();
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            PrepareCriteria::class,
            ($this->prepareCriteria)(
                project: new SpaceProject($this->createMock(Project::class)),
                manager: $this->createMock(ManagerInterface::class),
                bag: $this->createMock(ParametersBag::class),
            ),
        );
    }

    public function testInvokeWithEmptyCriteria(): void
    {
        $project = $this->createMock(Project::class);
        $spaceProject = $this->createMock(SpaceProject::class);
        $spaceProject->project = $project;
        $spaceProject->expects($this->once())
            ->method('getId')
            ->willReturn('project-123');

        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->exactly(2))
            ->method('set')
            ->willReturnCallback(function ($key, $value) use ($bag, $spaceProject) {
                $this->assertContains($key, ['projectId', 'project']);
                if ('projectId' === $key) {
                    $this->assertSame('project-123', $value);
                } elseif ('project' === $key) {
                    $this->assertSame($spaceProject, $value);
                }
                return $bag;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) use ($project) {
                    return isset($workplan['criteria'])
                        && is_array($workplan['criteria'])
                        && isset($workplan['criteria']['project'])
                        && $workplan['criteria']['project'] instanceof ObjectReference;
                })
            );

        $result = ($this->prepareCriteria)(
            project: $spaceProject,
            manager: $manager,
            bag: $bag,
            criteria: [],
        );

        $this->assertInstanceOf(PrepareCriteria::class, $result);
    }

    public function testInvokeWithExistingCriteria(): void
    {
        $project = $this->createMock(Project::class);
        $spaceProject = $this->createMock(SpaceProject::class);
        $spaceProject->project = $project;
        $spaceProject->expects($this->once())
            ->method('getId')
            ->willReturn('project-456');

        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->exactly(2))
            ->method('set')
            ->willReturnCallback(function ($key, $value) use ($bag, $spaceProject) {
                $this->assertContains($key, ['projectId', 'project']);
                if ('projectId' === $key) {
                    $this->assertSame('project-456', $value);
                } elseif ('project' === $key) {
                    $this->assertSame($spaceProject, $value);
                }
                return $bag;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) use ($project) {
                    return isset($workplan['criteria'])
                        && is_array($workplan['criteria'])
                        && isset($workplan['criteria']['project'])
                        && $workplan['criteria']['project'] instanceof ObjectReference
                        && isset($workplan['criteria']['status'])
                        && 'active' === $workplan['criteria']['status']
                        && isset($workplan['criteria']['name'])
                        && 'test' === $workplan['criteria']['name'];
                })
            );

        $result = ($this->prepareCriteria)(
            project: $spaceProject,
            manager: $manager,
            bag: $bag,
            criteria: ['status' => 'active', 'name' => 'test'],
        );

        $this->assertInstanceOf(PrepareCriteria::class, $result);
    }

    public function testInvokeWithCriteriaContainingProject(): void
    {
        $project = $this->createMock(Project::class);
        $spaceProject = $this->createMock(SpaceProject::class);
        $spaceProject->project = $project;
        $spaceProject->expects($this->once())
            ->method('getId')
            ->willReturn('project-789');

        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->exactly(2))
            ->method('set')
            ->willReturnCallback(function ($key, $value) use ($bag, $spaceProject) {
                $this->assertContains($key, ['projectId', 'project']);
                if ('projectId' === $key) {
                    $this->assertSame('project-789', $value);
                } elseif ('project' === $key) {
                    $this->assertSame($spaceProject, $value);
                }
                return $bag;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) use ($project) {
                    // The project from criteria should be overridden by ObjectReference
                    return isset($workplan['criteria'])
                        && is_array($workplan['criteria'])
                        && isset($workplan['criteria']['project'])
                        && 'old-value' === $workplan['criteria']['project'];
                })
            );

        $result = ($this->prepareCriteria)(
            project: $spaceProject,
            manager: $manager,
            bag: $bag,
            criteria: ['project' => 'old-value'],
        );

        $this->assertInstanceOf(PrepareCriteria::class, $result);
    }
}
