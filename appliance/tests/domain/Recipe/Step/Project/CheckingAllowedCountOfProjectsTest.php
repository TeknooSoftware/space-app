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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Loader\ProjectLoader;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\Config\SubscriptionPlan;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Recipe\Step\Project\CheckingAllowedCountOfProjects;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(CheckingAllowedCountOfProjects::class)]
class CheckingAllowedCountOfProjectsTest extends TestCase
{
    private CheckingAllowedCountOfProjects $checkingAllowedCountOfProjects;

    private ProjectLoader&MockObject $projectLoader;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->checkingAllowedCountOfProjects = new CheckingAllowedCountOfProjects(
            projectLoader: $this->projectLoader = $this->createMock(ProjectLoader::class),
        );
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            CheckingAllowedCountOfProjects::class,
            ($this->checkingAllowedCountOfProjects)(
                $this->createMock(ManagerInterface::class),
                new SpaceAccount(
                    account: $this->createMock(Account::class),
                    environments: []
                ),
                new SubscriptionPlan(
                    id: 'foo',
                    name: 'Foo',
                    quotas: [
                        [
                            'category' => 'compute',
                            'type' => 'cpu',
                            'capacity' => '5',
                            'require' => '2',
                        ]
                    ]
                ),
            ),
        );
    }
}
