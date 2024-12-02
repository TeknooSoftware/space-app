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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\PersistedVariable;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Loader\AccountPersistedVariableLoader;
use Teknoo\Space\Loader\ProjectPersistedVariableLoader;
use Teknoo\Space\Object\DTO\NewJob;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Recipe\Step\PersistedVariable\LoadPersistedVariablesForJob;

/**
 * Class LoadPersistedVariablesForJobTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(LoadPersistedVariablesForJob::class)]
class LoadPersistedVariablesForJobTest extends TestCase
{
    private LoadPersistedVariablesForJob $loadPersistedVariablesForJob;

    private AccountPersistedVariableLoader|MockObject $loaderAccountPV;

    private ProjectPersistedVariableLoader|MockObject $loaderPV;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loaderAccountPV = $this->createMock(AccountPersistedVariableLoader::class);
        $this->loaderPV = $this->createMock(ProjectPersistedVariableLoader::class);
        $this->loadPersistedVariablesForJob = new LoadPersistedVariablesForJob($this->loaderAccountPV, $this->loaderPV);
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            LoadPersistedVariablesForJob::class,
            ($this->loadPersistedVariablesForJob)(
                $this->createMock(ManagerInterface::class),
                new SpaceProject($this->createMock(Project::class)),
                $this->createMock(NewJob::class),
            )
        );
    }
}
