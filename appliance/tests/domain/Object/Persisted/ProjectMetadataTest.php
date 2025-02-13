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

namespace Teknoo\Space\Tests\Unit\Object\Persisted;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Object\Persisted\ProjectMetadata;

/**
 * Class ProjectMetadataTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ProjectMetadata::class)]
class ProjectMetadataTest extends TestCase
{
    private ProjectMetadata $projectMetadata;

    private Project|MockObject $project;

    private string $projectUrl;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->project = $this->createMock(Project::class);
        $this->projectUrl = '42';
        $this->projectMetadata = new ProjectMetadata($this->project, $this->projectUrl);
    }

    public function testSetProject(): void
    {
        $expected = $this->createMock(Project::class);
        $property = (new ReflectionClass(ProjectMetadata::class))
            ->getProperty('project');
        $property->setAccessible(true);
        $this->projectMetadata->setProject($expected);
        self::assertEquals($expected, $property->getValue($this->projectMetadata));
    }

    public function testSetProjectUrl(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(ProjectMetadata::class))
            ->getProperty('projectUrl');
        $property->setAccessible(true);
        $this->projectMetadata->setProjectUrl($expected);
        self::assertEquals($expected, $property->getValue($this->projectMetadata));
    }

    public function testVisit(): void
    {
        $final = null;
        self::assertInstanceOf(
            ProjectMetadata::class,
            $this->projectMetadata->visit([
                'projectUrl' => function ($value) use (&$final) {
                    $final = $value;
                },
                'foo' => fn () => self::fail('Must be not called'),
            ]),
        );
        self::assertEquals(
            '42',
            $final,
        );
    }

    public function testExport(): void
    {
        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->once())
            ->method('set')
            ->with('projectUrl', 42);

        self::assertInstanceOf(
            ProjectMetadata::class,
            $this->projectMetadata->export($bag)
        );
    }
}
