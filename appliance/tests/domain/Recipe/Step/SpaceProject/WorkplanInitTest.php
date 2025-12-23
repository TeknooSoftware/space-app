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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\SpaceProject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Environment;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\Persisted\ProjectMetadata;
use Teknoo\Space\Recipe\Step\SpaceProject\WorkplanInit;

/**
 * Class WorkplanInitTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(WorkplanInit::class)]
class WorkplanInitTest extends TestCase
{
    private WorkplanInit $workplanInit;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();


        $this->workplanInit = new WorkplanInit();
    }

    public function testInvoke(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with($this->isArray());

        $this->assertInstanceOf(
            WorkplanInit::class,
            ($this->workplanInit)(
                $manager,
                $this->createStub(SpaceProject::class),
                $this->createStub(Project::class),
                $this->createStub(ProjectMetadata::class),
                false,
            )
        );
    }

    public function testInvokeWithProjectFromSpaceProject(): void
    {
        $project = $this->createStub(Project::class);
        $metadata = $this->createStub(ProjectMetadata::class);

        $spaceProject = $this->createStub(SpaceProject::class);
        $spaceProject->project = $project;
        $spaceProject->projectMetadata = $metadata;

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($data) use ($project, $metadata) {
                    return $data[Project::class] === $project
                        && $data[ProjectMetadata::class] === $metadata;
                })
            );

        $this->assertInstanceOf(
            WorkplanInit::class,
            ($this->workplanInit)(
                $manager,
                $spaceProject,
            )
        );
    }

    public function testInvokeWithProjectFromParameters(): void
    {
        $project = $this->createStub(Project::class);
        $metadata = $this->createStub(ProjectMetadata::class);

        $spaceProject = $this->createStub(SpaceProject::class);
        $spaceProject->project = $project;
        $spaceProject->projectMetadata = null;

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($data) use ($project, $metadata) {
                    return $data[Project::class] === $project
                        && $data[ProjectMetadata::class] === $metadata;
                })
            );

        $this->assertInstanceOf(
            WorkplanInit::class,
            ($this->workplanInit)(
                $manager,
                $spaceProject,
                $project,
                $metadata,
            )
        );
    }

    public function testInvokeWithPopulateFormOptions(): void
    {
        $environment = $this->createStub(Environment::class);
        $environment
            ->method('__toString')
            ->willReturn('prod');

        $project = $this->createMock(Project::class);
        $project->expects($this->once())
            ->method('isInState')
            ->willReturnCallback(function ($states, $callback) use ($project) {
                $callback();
                return $project;
            });
        $project->expects($this->once())
            ->method('__call')
            ->with('listMeYourEnvironments')
            ->willReturnCallback(function ($name, array $callback) use ($environment, $project) {
                $callback[0]($environment);
                return $project;
            });

        $spaceProject = $this->createStub(SpaceProject::class);
        $spaceProject->project = $project;

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($data) {
                    return isset($data['formOptions'])
                        && isset($data['formOptions']['environmentsList'])
                        && 'prod' === $data['formOptions']['environmentsList']['prod'];
                })
            );

        $this->assertInstanceOf(
            WorkplanInit::class,
            ($this->workplanInit)(
                $manager,
                $spaceProject,
                null,
                null,
                true,
            )
        );
    }

    public function testInvokeWithPopulateFormOptionsAndExistingOptions(): void
    {
        $environment = $this->createStub(Environment::class);
        $environment
            ->method('__toString')
            ->willReturn('staging');

        $project = $this->createMock(Project::class);
        $project->expects($this->once())
            ->method('isInState')
            ->willReturnCallback(function ($states, $callback) use ($project) {
                $callback();
                return $project;
            });

        $project->expects($this->once())
            ->method('__call')
            ->with('listMeYourEnvironments')
            ->willReturnCallback(function ($name, array $callback) use ($environment, $project) {
                $callback[0]($environment);
                return $project;
            });

        $spaceProject = $this->createStub(SpaceProject::class);
        $spaceProject->project = $project;

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($data) {
                    return isset($data['formOptions'])
                        && 'bar' === $data['formOptions']['foo']
                        && isset($data['formOptions']['environmentsList'])
                        && 'staging' === $data['formOptions']['environmentsList']['staging'];
                })
            );

        $this->assertInstanceOf(
            WorkplanInit::class,
            ($this->workplanInit)(
                manager: $manager,
                spaceProject: $spaceProject,
                projectObject: null,
                metadata: null,
                populateFormOptions: true,
                formOptions: ['foo' => 'bar'],
            )
        );
    }

    public function testInvokeWithPopulateFormOptionsFalse(): void
    {
        $project = $this->createMock(Project::class);
        $project->expects($this->never())
            ->method('isInState');
        $project->expects($this->never())
            ->method('__call')
            ->with('listMeYourEnvironments');

        $spaceProject = $this->createStub(SpaceProject::class);
        $spaceProject->project = $project;

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($data) {
                    return !isset($data['formOptions']);
                })
            );

        $this->assertInstanceOf(
            WorkplanInit::class,
            ($this->workplanInit)(
                $manager,
                $spaceProject,
                null,
                null,
                false,
            )
        );
    }

    public function testInvokeWithMultipleEnvironments(): void
    {
        $env1 = $this->createStub(Environment::class);
        $env1
            ->method('__toString')
            ->willReturn('dev');

        $env2 = $this->createStub(Environment::class);
        $env2
            ->method('__toString')
            ->willReturn('prod');

        $project = $this->createMock(Project::class);
        $project->expects($this->once())
            ->method('isInState')
            ->willReturnCallback(function ($states, $callback) use ($project) {
                $callback();
                return $project;
            });
        $project->expects($this->once())
            ->method('__call')
            ->with('listMeYourEnvironments')
            ->willReturnCallback(function ($name, $callback) use ($env1, $env2, $project) {
                $callback[0]($env1);
                $callback[0]($env2);
                return $project;
            });

        $spaceProject = $this->createStub(SpaceProject::class);
        $spaceProject->project = $project;

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($data) {
                    return isset($data['formOptions']['environmentsList'])
                        && 'dev' === $data['formOptions']['environmentsList']['dev']
                        && 'prod' === $data['formOptions']['environmentsList']['prod'];
                })
            );

        $this->assertInstanceOf(
            WorkplanInit::class,
            ($this->workplanInit)(
                $manager,
                $spaceProject,
                null,
                null,
                true,
            )
        );
    }
}
