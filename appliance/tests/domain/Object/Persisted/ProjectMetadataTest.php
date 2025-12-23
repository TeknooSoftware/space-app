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

namespace Teknoo\Space\Tests\Unit\Object\Persisted;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Normalizer\EastNormalizerInterface;
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

    private Project&Stub $project;

    private string $projectUrl;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->project = $this->createStub(Project::class);
        $this->projectUrl = '42';
        $this->projectMetadata = new ProjectMetadata($this->project, $this->projectUrl);
    }

    public function testConstructorWithProject(): void
    {
        $project = $this->createStub(Project::class);
        $projectUrl = 'https://example.com';
        $metadata = new ProjectMetadata($project, $projectUrl);

        $this->assertInstanceOf(ProjectMetadata::class, $metadata);
    }

    public function testConstructorWithNullProject(): void
    {
        $projectUrl = 'https://example.com';
        $metadata = new ProjectMetadata(null, $projectUrl);

        $this->assertInstanceOf(ProjectMetadata::class, $metadata);
    }

    public function testConstructorWithNullProjectUrl(): void
    {
        $project = $this->createStub(Project::class);
        $metadata = new ProjectMetadata($project, null);

        $this->assertInstanceOf(ProjectMetadata::class, $metadata);
    }

    public function testSetProject(): void
    {
        $newProject = $this->createStub(Project::class);
        $result = $this->projectMetadata->setProject($newProject);

        $this->assertInstanceOf(ProjectMetadata::class, $result);
        $this->assertSame($this->projectMetadata, $result);
    }

    public function testSetProjectUrl(): void
    {
        $newUrl = 'https://new-url.com';
        $result = $this->projectMetadata->setProjectUrl($newUrl);

        $this->assertInstanceOf(ProjectMetadata::class, $result);
        $this->assertSame($this->projectMetadata, $result);
    }

    public function testSetProjectUrlWithNull(): void
    {
        $result = $this->projectMetadata->setProjectUrl(null);

        $this->assertInstanceOf(ProjectMetadata::class, $result);
        $this->assertSame($this->projectMetadata, $result);
    }

    public function testVisit(): void
    {
        $final = null;
        $this->assertInstanceOf(
            ProjectMetadata::class,
            $this->projectMetadata->visit([
                'projectUrl' => function ($value) use (&$final): void {
                    $final = $value;
                },
                'foo' => fn () => self::fail('Must be not called'),
            ]),
        );
        $this->assertEquals(
            '42',
            $final,
        );
    }

    public function testExport(): void
    {
        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->once())
            ->method('set')
            ->with('projectUrl', '42');

        $this->assertInstanceOf(
            ProjectMetadata::class,
            $this->projectMetadata->export($bag)
        );
    }

    public function testExportToMeData(): void
    {
        $normalizer = $this->createMock(EastNormalizerInterface::class);

        $normalizer->expects($this->once())
            ->method('injectData')
            ->with(
                $this->callback(function ($data) {
                    $this->assertIsArray($data);
                    $this->assertArrayHasKey('@class', $data);
                    // projectUrl is only in 'crud' group, not in 'default'
                    return true;
                })
            );

        $result = $this->projectMetadata->exportToMeData($normalizer, []);

        $this->assertInstanceOf(ProjectMetadata::class, $result);
        $this->assertSame($this->projectMetadata, $result);
    }

    public function testExportToMeDataWithCrudGroup(): void
    {
        $normalizer = $this->createMock(EastNormalizerInterface::class);

        $normalizer->expects($this->once())
            ->method('injectData')
            ->with(
                $this->callback(function ($data) {
                    $this->assertIsArray($data);
                    $this->assertArrayHasKey('@class', $data);
                    $this->assertArrayHasKey('projectUrl', $data);
                    $this->assertEquals('42', $data['projectUrl']);
                    return true;
                })
            );

        $result = $this->projectMetadata->exportToMeData($normalizer, ['groups' => ['crud']]);

        $this->assertInstanceOf(ProjectMetadata::class, $result);
    }
}
