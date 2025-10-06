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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\AccountEnvironment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\Config\SubscriptionPlan;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Recipe\Step\AccountEnvironment\CheckingAllowedCountOfEnvs;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(CheckingAllowedCountOfEnvs::class)]
class CheckingAllowedCountOfEnvsTest extends TestCase
{
    private CheckingAllowedCountOfEnvs $checkingAllowedCountOfEnvs;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->checkingAllowedCountOfEnvs = new CheckingAllowedCountOfEnvs();
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            CheckingAllowedCountOfEnvs::class,
            ($this->checkingAllowedCountOfEnvs)(
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

    public function testInvokeWithNullPlan(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');

        $this->assertInstanceOf(
            CheckingAllowedCountOfEnvs::class,
            ($this->checkingAllowedCountOfEnvs)(
                manager: $manager,
                spaceAccount: new SpaceAccount(
                    account: $this->createMock(Account::class),
                    environments: []
                ),
                plan: null,
            ),
        );
    }

    public function testInvokeWithOverflow(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($error) {
                    return $error instanceof \OverflowException
                        && str_contains($error->getMessage(), 'Test Plan')
                        && str_contains($error->getMessage(), 'accepts only 1 environments')
                        && 400 === $error->getCode();
                })
            );

        $this->assertInstanceOf(
            CheckingAllowedCountOfEnvs::class,
            ($this->checkingAllowedCountOfEnvs)(
                manager: $manager,
                spaceAccount: new SpaceAccount(
                    account: $this->createMock(Account::class),
                    environments: [
                        (object)['id' => '1'],
                        (object)['id' => '2'],
                    ]
                ),
                plan: new SubscriptionPlan(
                    id: 'test',
                    name: 'Test Plan',
                    quotas: [],
                    envsCountAllowed: 1
                ),
            ),
        );
    }

    public function testInvokeWithNullPlanAndEnvironments(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');

        $this->assertInstanceOf(
            CheckingAllowedCountOfEnvs::class,
            ($this->checkingAllowedCountOfEnvs)(
                manager: $manager,
                spaceAccount: new SpaceAccount(
                    account: $this->createMock(Account::class),
                    environments: [
                        (object)['id' => '1'],
                        (object)['id' => '2'],
                    ]
                ),
                plan: null,
            ),
        );
    }

    public function testInvokeWithZeroEnvsAllowed(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');

        $this->assertInstanceOf(
            CheckingAllowedCountOfEnvs::class,
            ($this->checkingAllowedCountOfEnvs)(
                manager: $manager,
                spaceAccount: new SpaceAccount(
                    account: $this->createMock(Account::class),
                    environments: [
                        (object)['id' => '1'],
                        (object)['id' => '2'],
                    ]
                ),
                plan: new SubscriptionPlan(
                    id: 'test',
                    name: 'Test Plan',
                    quotas: [],
                    envsCountAllowed: 0
                ),
            ),
        );
    }

    public function testInvokeWithEmptyEnvironments(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');

        $this->assertInstanceOf(
            CheckingAllowedCountOfEnvs::class,
            ($this->checkingAllowedCountOfEnvs)(
                manager: $manager,
                spaceAccount: new SpaceAccount(
                    account: $this->createMock(Account::class),
                    environments: []
                ),
                plan: new SubscriptionPlan(
                    id: 'test',
                    name: 'Test Plan',
                    quotas: [],
                    envsCountAllowed: 1
                ),
            ),
        );
    }

    public function testInvokeWithEqualLimit(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');

        $this->assertInstanceOf(
            CheckingAllowedCountOfEnvs::class,
            ($this->checkingAllowedCountOfEnvs)(
                manager: $manager,
                spaceAccount: new SpaceAccount(
                    account: $this->createMock(Account::class),
                    environments: [
                        (object)['id' => '1'],
                    ]
                ),
                plan: new SubscriptionPlan(
                    id: 'test',
                    name: 'Test Plan',
                    quotas: [],
                    envsCountAllowed: 1
                ),
            ),
        );
    }
}
