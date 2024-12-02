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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\NewJob;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\NewJob;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Recipe\Step\NewJob\NewJobSetDefaults;

/**
 * Class JobSetDefaultsTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(NewJobSetDefaults::class)]
class NewJobSetDefaultsTest extends TestCase
{
    private NewJobSetDefaults $newJobSetDefaults;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->newJobSetDefaults = new NewJobSetDefaults(
            $this->createMock(ClusterCatalog::class)
        );
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            NewJobSetDefaults::class,
            ($this->newJobSetDefaults)(
                project: new SpaceProject($this->createMock(Project::class)),
                newJob: $this->createMock(NewJob::class),
            ),
        );
    }
}
