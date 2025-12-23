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
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Contracts\Object\ImageRegistryInterface;
use Teknoo\East\Paas\Contracts\Object\SourceRepositoryInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\Cluster;
use Teknoo\East\Paas\Object\Environment;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Object\DTO\NewJob;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Recipe\Step\Job\PrepareNewJobForm;

/**
 * Class PrepareNewJobFormTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(PrepareNewJobForm::class)]
class PrepareNewJobFormTest extends TestCase
{
    private PrepareNewJobForm $prepareNewJobForm;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();


        $this->prepareNewJobForm = new PrepareNewJobForm();
    }

    public function testInvoke(): void
    {
        $project = new Project($this->createStub(Account::class));
        $project->setSourceRepository($this->createStub(SourceRepositoryInterface::class))
            ->setImagesRegistry($this->createStub(ImageRegistryInterface::class))
            ->setClusters([$this->createStub(Cluster::class)]);

        $this->assertInstanceOf(
            PrepareNewJobForm::class,
            ($this->prepareNewJobForm)(
                $this->createStub(ManagerInterface::class),
                new SpaceProject($project),
                $this->createStub(NewJob::class),
                $this->createStub(ParametersBag::class),
                'foo',
            ),
        );
    }

    public function testInvokeWithDirectProject(): void
    {
        $account = $this->createStub(Account::class);
        $account->method('getId')->willReturn('account-123');

        $project = new Project($account);
        $project->setSourceRepository($this->createStub(SourceRepositoryInterface::class))
            ->setImagesRegistry($this->createStub(ImageRegistryInterface::class))
            ->setClusters([$this->createStub(Cluster::class)])
            ->setId('project-123');

        $newJob = $this->createStub(NewJob::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan['formOptions'])
                        && isset($workplan['formOptions']['environmentsList'])
                        && is_array($workplan['formOptions']['environmentsList']);
                })
            );

        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->once())
            ->method('set')
            ->with('project', $project);

        $result = ($this->prepareNewJobForm)(
            $manager,
            $project,
            $newJob,
            $bag,
        );

        $this->assertInstanceOf(PrepareNewJobForm::class, $result);
    }

    public function testInvokeWithFormActionRoute(): void
    {
        $account = $this->createStub(Account::class);
        $account->method('getId')->willReturn('account-123');

        $project = new Project($account);
        $project->setSourceRepository($this->createStub(SourceRepositoryInterface::class))
            ->setImagesRegistry($this->createStub(ImageRegistryInterface::class))
            ->setClusters([$this->createStub(Cluster::class)])
            ->setId('project-456');

        $newJob = $this->createStub(NewJob::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan');

        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->exactly(3))
            ->method('set')
            ->willReturnCallback(function ($key, $value) use ($project, $bag) {
                match ($key) {
                    'project' => $this->assertSame($project, $value),
                    'formActionRoute' => $this->assertSame('my_route', $value),
                    'formActionRouteParams' => $this->assertSame(['projectId' => 'project-456'], $value),
                    default => $this->fail("Unexpected key: $key"),
                };
                return $bag;
            });

        $result = ($this->prepareNewJobForm)(
            $manager,
            $project,
            $newJob,
            $bag,
            'my_route',
        );

        $this->assertInstanceOf(PrepareNewJobForm::class, $result);
    }

    public function testInvokeWithFormOptionsAndEnv(): void
    {
        $account = $this->createStub(Account::class);
        $account->method('getId')->willReturn('account-123');

        $project = new Project($account);
        $project->setSourceRepository($this->createStub(SourceRepositoryInterface::class))
            ->setImagesRegistry($this->createStub(ImageRegistryInterface::class))
            ->setClusters([new Cluster(project: $project, environment: new Environment('prod'))])
            ->setId('project-789');

        $newJob = $this->createStub(NewJob::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan['formOptions'])
                        && isset($workplan['formOptions']['environmentsList'])
                        && ['prod' => 'prod'] === $workplan['formOptions']['environmentsList']
                        && isset($workplan['formOptions']['customOption'])
                        && 'customValue' === $workplan['formOptions']['customOption'];
                })
            );

        $bag = $this->createStub(ParametersBag::class);

        $result = ($this->prepareNewJobForm)(
            $manager,
            $project,
            $newJob,
            $bag,
            null,
            ['customOption' => 'customValue'],
        );

        $this->assertInstanceOf(PrepareNewJobForm::class, $result);
    }

    public function testInvokeWithNonRunnableProject(): void
    {
        $project = new Project($this->createStub(Account::class));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Project is not fully configured');

        ($this->prepareNewJobForm)(
            $this->createStub(ManagerInterface::class),
            $project,
            $this->createStub(NewJob::class),
            $this->createStub(ParametersBag::class),
        );
    }

    public function testInvokeWithSpaceProjectWrapper(): void
    {
        $account = $this->createStub(Account::class);
        $account->method('getId')->willReturn('account-999');

        $project = new Project($account);
        $project->setSourceRepository($this->createStub(SourceRepositoryInterface::class))
            ->setImagesRegistry($this->createStub(ImageRegistryInterface::class))
            ->setClusters([$this->createStub(Cluster::class)])
            ->setId('project-999');

        $spaceProject = new SpaceProject($project);

        $newJob = $this->createStub(NewJob::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan');

        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->once())
            ->method('set')
            ->with('project', $project);

        $result = ($this->prepareNewJobForm)(
            $manager,
            $spaceProject,
            $newJob,
            $bag,
        );

        $this->assertInstanceOf(PrepareNewJobForm::class, $result);
    }

    public function testInvokeWithEmptyFormActionRoute(): void
    {
        $account = $this->createStub(Account::class);
        $account->method('getId')->willReturn('account-empty');

        $project = new Project($account);
        $project->setSourceRepository($this->createStub(SourceRepositoryInterface::class))
            ->setImagesRegistry($this->createStub(ImageRegistryInterface::class))
            ->setClusters([$this->createStub(Cluster::class)])
            ->setId('project-empty');

        $newJob = $this->createStub(NewJob::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan');

        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->once())
            ->method('set')
            ->with('project', $project);

        $result = ($this->prepareNewJobForm)(
            $manager,
            $project,
            $newJob,
            $bag,
            '',
        );

        $this->assertInstanceOf(PrepareNewJobForm::class, $result);
    }
}
