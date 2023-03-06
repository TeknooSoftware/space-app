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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\SpaceProject;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\Persisted\ProjectMetadata;
use Teknoo\Space\Recipe\Step\SpaceProject\WorkplanInit;

/**
 * Class WorkplanInitTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Recipe\Step\SpaceProject\WorkplanInit
 */
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
        self::assertInstanceOf(
            WorkplanInit::class,
            ($this->workplanInit)(
                $this->createMock(ManagerInterface::class),
                $this->createMock(SpaceProject::class),
                $this->createMock(Project::class),
                $this->createMock(ProjectMetadata::class),
                true,
            )
        );
    }
}
