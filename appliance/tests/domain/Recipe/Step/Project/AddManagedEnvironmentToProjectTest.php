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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Project;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Recipe\Step\Project\AddManagedEnvironmentToProject;

/**
 * Class AddManagedEnvironmentToProjectTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AddManagedEnvironmentToProject::class)]
class AddManagedEnvironmentToProjectTest extends TestCase
{
    private AddManagedEnvironmentToProject $addManagedEnvironmentToProject;


    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->addManagedEnvironmentToProject = new AddManagedEnvironmentToProject();
    }

    public function testInvoke(): void
    {
        $wallet = new AccountWallet(
            [$this->createMock(AccountEnvironment::class)]
        );

        $this->assertInstanceOf(
            AddManagedEnvironmentToProject::class,
            ($this->addManagedEnvironmentToProject)(
                $this->createMock(ManagerInterface::class),
                new SpaceProject($this->createMock(Project::class)),
                $wallet,
                $this->createMock(ClusterCatalog::class),
            )
        );
    }
}
