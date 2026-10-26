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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Account;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Recipe\Step\Account\LoadSpaceAccountFromAccount;

/**
 * Class LoadSpaceAccountFromAccountTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(LoadSpaceAccountFromAccount::class)]
class LoadSpaceAccountFromAccountTest extends TestCase
{
    private LoadSpaceAccountFromAccount $loadSpaceAccountFromAccount;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadSpaceAccountFromAccount = new LoadSpaceAccountFromAccount();
    }

    private function createAccount(string $id): Account
    {
        $account = $this->createStub(Account::class);
        $account->method('getId')->willReturn($id);

        return $account;
    }

    public function testInvokeWithMatchingSpaceAccountAndAccountInstance(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('updateWorkPlan');
        $manager->expects($this->never())
            ->method('error');

        $result = ($this->loadSpaceAccountFromAccount)(
            manager: $manager,
            accountInstance: $this->createAccount('account-789'),
            spaceAccount: new SpaceAccount($this->createAccount('account-789')),
        );

        $this->assertInstanceOf(LoadSpaceAccountFromAccount::class, $result);
    }

    public function testInvokeWithNullAccount(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($exception) {
                    return $exception instanceof \BadMethodCallException
                        && 'An account is mandatory to create a project' === $exception->getMessage()
                        && 404 === $exception->getCode();
                })
            );

        $result = ($this->loadSpaceAccountFromAccount)(
            manager: $manager,
            accountInstance: null,
            spaceAccount: null,
        );

        $this->assertInstanceOf(LoadSpaceAccountFromAccount::class, $result);
    }

    public function testInvokeWithAnOtherAccountThanTheAccountOfTheUser(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($exception) {
                    return $exception instanceof \BadMethodCallException
                        && 'The account is not the account of the user' === $exception->getMessage()
                        && 403 === $exception->getCode();
                })
            );

        $result = ($this->loadSpaceAccountFromAccount)(
            manager: $manager,
            accountInstance: $this->createAccount('account-123'),
            spaceAccount: new SpaceAccount($this->createAccount('account-456')),
        );

        $this->assertInstanceOf(LoadSpaceAccountFromAccount::class, $result);
    }

    public function testInvokeForAnAdministratorWithoutAccount(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($exception) {
                    return $exception instanceof \BadMethodCallException
                        && 403 === $exception->getCode();
                })
            );
        $manager->expects($this->never())
            ->method('updateWorkPlan');

        $result = ($this->loadSpaceAccountFromAccount)(
            manager: $manager,
            accountInstance: $this->createAccount('account-123'),
            spaceAccount: null,
        );

        $this->assertInstanceOf(LoadSpaceAccountFromAccount::class, $result);
    }
}
