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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Job;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
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
        self::assertInstanceOf(
            PrepareCriteria::class,
            ($this->prepareCriteria)(
                new SpaceProject($this->createMock(Project::class)),
                $this->createMock(ManagerInterface::class),
                $this->createMock(ParametersBag::class),
            ),
        );
    }
}
