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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Form\DataMapper;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Infrastructures\Symfony\Form\DataMapper\ProjectVarsMapper;
use Teknoo\Space\Object\DTO\JobVar;
use Teknoo\Space\Object\DTO\JobVarsSet;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\Persisted\PersistedVariable;

/**
 * Class ProjectVarsMapperTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Infrastructures\Symfony\Form\DataMapper\ProjectVarsMapper
 * @covers \Teknoo\Space\Infrastructures\Symfony\Form\DataMapper\AbstractVarsMapper
 */
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
            $this->createMock(PersistedVariable::class),
            $this->createMock(PersistedVariable::class),
        ];

        self::assertInstanceOf(
            ProjectVarsMapper::class,
            $this->projectVarsType->mapDataToForms(
                $project,
                new ArrayIterator(
                    [
                        'sets' => $this->createMock(FormInterface::class),
                    ]
                ),
            ),
        );
    }

    public function testMapFormsToData(): void
    {
        $project = new SpaceProject($this->createMock(Project::class));
        $project->variables = [
            $this->createMock(PersistedVariable::class),
            $this->createMock(PersistedVariable::class),
        ];

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::any())
            ->method('getData')
            ->willReturn(
                [
                    new JobVarsSet(
                        'foo',
                        [
                            new JobVar(
                                'foo',
                                'bar',
                                'foo',
                                false,
                                true,
                                true,
                                $this->createMock(PersistedVariable::class),
                            ),
                            new JobVar(
                                null,
                                'bar',
                                'foo',
                                true,
                                true,
                                false,
                                $this->createMock(PersistedVariable::class),
                            ),
                        ]
                    )
                ]
            );
        self::assertInstanceOf(
            ProjectVarsMapper::class,
            $this->projectVarsType->mapFormsToData(
                new ArrayIterator(
                    [
                        'sets' => $form,
                    ]
                ),
                $project,
            ),
        );
    }
}
