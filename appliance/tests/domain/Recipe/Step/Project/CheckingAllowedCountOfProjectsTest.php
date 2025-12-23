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
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Loader\ProjectLoader;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\Config\SubscriptionPlan;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Recipe\Step\Project\CheckingAllowedCountOfProjects;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

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

    #[AllowMockObjectsWithoutExpectations]
    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            CheckingAllowedCountOfProjects::class,
            ($this->checkingAllowedCountOfProjects)(
                $this->createStub(ManagerInterface::class),
                new SpaceAccount(
                    account: $this->createStub(Account::class),
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

    public function testInvokeWithNullAccount(): void
    {
        $this->projectLoader->expects($this->never())
            ->method('fetch');

        $this->assertInstanceOf(
            CheckingAllowedCountOfProjects::class,
            ($this->checkingAllowedCountOfProjects)(
                $this->createStub(ManagerInterface::class),
                null,
                new SubscriptionPlan(
                    id: 'foo',
                    name: 'Foo',
                    quotas: [],
                    projectsCountAllowed: 5
                ),
            ),
        );
    }

    public function testInvokeWithNullPlan(): void
    {
        $this->projectLoader->expects($this->never())
            ->method('fetch');

        $this->assertInstanceOf(
            CheckingAllowedCountOfProjects::class,
            ($this->checkingAllowedCountOfProjects)(
                $this->createStub(ManagerInterface::class),
                new SpaceAccount(
                    account: $this->createStub(Account::class),
                    environments: []
                ),
                null,
            ),
        );
    }

    public function testInvokeWithZeroProjectsAllowed(): void
    {
        $this->projectLoader->expects($this->never())
            ->method('fetch');

        $this->assertInstanceOf(
            CheckingAllowedCountOfProjects::class,
            ($this->checkingAllowedCountOfProjects)(
                $this->createStub(ManagerInterface::class),
                new SpaceAccount(
                    account: $this->createStub(Account::class),
                    environments: []
                ),
                new SubscriptionPlan(
                    id: 'foo',
                    name: 'Foo',
                    quotas: [],
                    projectsCountAllowed: 0
                ),
            ),
        );
    }

    public function testInvokeWithAccountDirectly(): void
    {
        $this->projectLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($query, $promise) {
                $promise->success(2);

                return $this->projectLoader;
            });

        $this->assertInstanceOf(
            CheckingAllowedCountOfProjects::class,
            ($this->checkingAllowedCountOfProjects)(
                $this->createStub(ManagerInterface::class),
                $this->createStub(Account::class),
                new SubscriptionPlan(
                    id: 'foo',
                    name: 'Foo',
                    quotas: [],
                    projectsCountAllowed: 5
                ),
            ),
        );
    }

    public function testInvokeWithSpaceAccountAndProjectsCountBelowLimit(): void
    {
        $this->projectLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($query, $promise) {
                $promise->success(2);

                return $this->projectLoader;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('error');

        $this->assertInstanceOf(
            CheckingAllowedCountOfProjects::class,
            ($this->checkingAllowedCountOfProjects)(
                $manager,
                new SpaceAccount(
                    account: $this->createStub(Account::class),
                    environments: []
                ),
                new SubscriptionPlan(
                    id: 'foo',
                    name: 'Foo',
                    quotas: [],
                    projectsCountAllowed: 5
                ),
            ),
        );
    }

    public function testInvokeWithProjectsCountAtLimit(): void
    {
        $this->projectLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($query, $promise) {
                $promise->success(5);

                return $this->projectLoader;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($e) {
                    return $e instanceof \OverflowException
                        && 'The plan Foo accepts only 5 projects' === $e->getMessage()
                        && 400 === $e->getCode();
                })
            );

        $this->assertInstanceOf(
            CheckingAllowedCountOfProjects::class,
            ($this->checkingAllowedCountOfProjects)(
                $manager,
                new SpaceAccount(
                    account: $this->createStub(Account::class),
                    environments: []
                ),
                new SubscriptionPlan(
                    id: 'foo',
                    name: 'Foo',
                    quotas: [],
                    projectsCountAllowed: 5
                ),
            ),
        );
    }

    public function testInvokeWithProjectsCountAboveLimit(): void
    {
        $this->projectLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($query, $promise) {
                $promise->success(10);

                return $this->projectLoader;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($e) {
                    return $e instanceof \OverflowException
                        && 'The plan Foo accepts only 5 projects' === $e->getMessage();
                })
            );

        $this->assertInstanceOf(
            CheckingAllowedCountOfProjects::class,
            ($this->checkingAllowedCountOfProjects)(
                $manager,
                new SpaceAccount(
                    account: $this->createStub(Account::class),
                    environments: []
                ),
                new SubscriptionPlan(
                    id: 'foo',
                    name: 'Foo',
                    quotas: [],
                    projectsCountAllowed: 5
                ),
            ),
        );
    }

    public function testInvokeWithPromiseFailure(): void
    {
        $exception = new \RuntimeException('Test error');

        $this->projectLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($query, $promise) use ($exception) {
                $promise->fail($exception);

                return $this->projectLoader;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with($this->identicalTo($exception));

        $this->assertInstanceOf(
            CheckingAllowedCountOfProjects::class,
            ($this->checkingAllowedCountOfProjects)(
                $manager,
                new SpaceAccount(
                    account: $this->createStub(Account::class),
                    environments: []
                ),
                new SubscriptionPlan(
                    id: 'foo',
                    name: 'Foo',
                    quotas: [],
                    projectsCountAllowed: 5
                ),
            ),
        );
    }
}
