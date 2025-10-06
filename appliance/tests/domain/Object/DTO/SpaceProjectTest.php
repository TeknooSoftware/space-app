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

namespace Teknoo\Space\Tests\Unit\Object\DTO;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Normalizer\EastNormalizerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\Persisted\ProjectMetadata;
use Teknoo\Space\Object\Persisted\ProjectPersistedVariable;

/**
 * Class SpaceProjectTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SpaceProject::class)]
class SpaceProjectTest extends TestCase
{
    private SpaceProject $spaceProject;

    private Project&MockObject $project;

    private ProjectMetadata&MockObject $projectMetadata;

    private iterable $variables;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->project = $this->createMock(Project::class);
        $this->projectMetadata = $this->createMock(ProjectMetadata::class);
        $this->variables = [];
        $this->spaceProject = new SpaceProject($this->project, $this->projectMetadata, $this->variables);
    }

    public function testGetId(): void
    {
        $this->project
            ->method('getId')
            ->willReturn('foo');

        $this->assertEquals(
            'foo',
            $this->spaceProject->getId()
        );
    }

    public function testGetAccount(): void
    {
        $this->project
            ->method('getAccount')
            ->willReturn($this->createMock(Account::class));

        $this->assertInstanceOf(
            Account::class,
            $this->spaceProject->getAccount(),
        );
    }

    public function testToString(): void
    {
        $this->project
            ->method('__toString')
            ->willReturn('foo');

        $this->assertEquals(
            'foo',
            (string) $this->spaceProject,
        );
    }

    public function testConstructorWithAccount(): void
    {
        $account = $this->createMock(Account::class);
        $spaceProject = new SpaceProject($account);

        $this->assertInstanceOf(Project::class, $spaceProject->project);
    }

    public function testConstructorWithAllParameters(): void
    {
        $variables = [$this->createMock(ProjectPersistedVariable::class)];
        $spaceProject = new SpaceProject(
            $this->project,
            $this->projectMetadata,
            $variables,
            'clusterName',
            'clusterEnv'
        );

        $this->assertSame($this->project, $spaceProject->project);
        $this->assertSame($this->projectMetadata, $spaceProject->projectMetadata);
        $this->assertSame($variables, $spaceProject->variables);
        $this->assertEquals('clusterName', $spaceProject->addClusterName);
        $this->assertEquals('clusterEnv', $spaceProject->addClusterEnv);
    }

    public function testExportToMeData(): void
    {
        $normalizer = $this->createMock(EastNormalizerInterface::class);
        $normalizer->expects($this->once())
            ->method('injectData')
            ->with($this->isArray())
            ->willReturnSelf();

        $result = $this->spaceProject->exportToMeData($normalizer);

        $this->assertInstanceOf(SpaceProject::class, $result);
        $this->assertSame($this->spaceProject, $result);
    }

    public function testExportToMeDataWithContext(): void
    {
        $normalizer = $this->createMock(EastNormalizerInterface::class);
        $normalizer->expects($this->once())
            ->method('injectData')
            ->with($this->isArray())
            ->willReturnSelf();

        $context = ['groups' => ['api', 'crud']];
        $result = $this->spaceProject->exportToMeData($normalizer, $context);

        $this->assertInstanceOf(SpaceProject::class, $result);
        $this->assertSame($this->spaceProject, $result);
    }
}
