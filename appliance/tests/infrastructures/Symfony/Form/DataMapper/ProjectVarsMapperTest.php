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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Form\DataMapper;

use ArrayIterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Infrastructures\Symfony\Form\DataMapper\AbstractVarsMapper;
use Teknoo\Space\Infrastructures\Symfony\Form\DataMapper\ProjectVarsMapper;
use Teknoo\Space\Object\DTO\JobVar;
use Teknoo\Space\Object\DTO\JobVarsSet;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\Persisted\ProjectPersistedVariable;

/**
 * Class ProjectVarsMapperTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AbstractVarsMapper::class)]
#[CoversClass(ProjectVarsMapper::class)]
class ProjectVarsMapperTest extends TestCase
{
    private ProjectVarsMapper $projectVarsType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->projectVarsType = new ProjectVarsMapper();
    }

    public function testMapDataToForms(): void
    {
        $project = new SpaceProject($this->createMock(Project::class));
        $project->variables = [
            $this->createMock(ProjectPersistedVariable::class),
            $this->createMock(ProjectPersistedVariable::class),
        ];

        $this->projectVarsType->mapDataToForms(
            $project,
            new ArrayIterator(
                [
                    'sets' => $this->createMock(FormInterface::class),
                ]
            ),
        );

        self::assertTrue(true);
    }

    public function testMapFormsToData(): void
    {
        $project = new SpaceProject($this->createMock(Project::class));
        $project->variables = [
            $this->createMock(ProjectPersistedVariable::class),
            $this->createMock(ProjectPersistedVariable::class),
        ];

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->any())
            ->method('getData')
            ->willReturn(
                [
                    new JobVarsSet(
                        envName: 'foo',
                        variables: [
                            new JobVar(
                                id: 'foo',
                                name: 'bar',
                                value: 'foo',
                                persisted: false,
                                secret: true,
                                wasSecret: true,
                                encryptionAlgorithm: 'rsa',
                                persistedVar: $this->createMock(ProjectPersistedVariable::class),
                            ),
                            new JobVar(
                                id: null,
                                name: 'bar',
                                value: 'foo',
                                persisted: true,
                                secret: true,
                                wasSecret: false,
                                persistedVar: $this->createMock(ProjectPersistedVariable::class),
                            ),
                        ]
                    )
                ]
            );

        $this->projectVarsType->mapFormsToData(
            new ArrayIterator(
                [
                    'sets' => $form,
                ]
            ),
            $project,
        );

        self::assertTrue(true);
    }
}
