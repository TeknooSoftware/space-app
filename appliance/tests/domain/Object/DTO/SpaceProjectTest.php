<?php

/*
 * Teknoo Space.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Object\DTO;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\Persisted\ProjectMetadata;

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

    private Project|MockObject $project;

    private ProjectMetadata|MockObject $projectMetadata;

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
            ->expects($this->any())
            ->method('getId')
            ->willReturn('foo');

        self::assertEquals(
            'foo',
            $this->spaceProject->getId()
        );
    }

    public function testGetAccount(): void
    {
        $this->project
            ->expects($this->any())
            ->method('getAccount')
            ->willReturn($this->createMock(Account::class));

        self::assertInstanceOf(
            Account::class,
            $this->spaceProject->getAccount(),
        );
    }

    public function testToString(): void
    {
        $this->project
            ->expects($this->any())
            ->method('__toString')
            ->willReturn('foo');

        self::assertEquals(
            'foo',
            (string) $this->spaceProject,
        );
    }
}
