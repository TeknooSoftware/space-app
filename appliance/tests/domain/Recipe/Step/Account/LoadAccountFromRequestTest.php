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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Loader\Meta\SpaceAccountLoader;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Recipe\Step\Account\LoadAccountFromRequest;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * Class LoadAccountFromRequestTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(LoadAccountFromRequest::class)]
class LoadAccountFromRequestTest extends TestCase
{
    private LoadAccountFromRequest $loadAccountFromRequest;

    private SpaceAccountLoader&MockObject $accountLoader;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->accountLoader = $this->createMock(SpaceAccountLoader::class);

        $this->loadAccountFromRequest = new LoadAccountFromRequest($this->accountLoader);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            LoadAccountFromRequest::class,
            ($this->loadAccountFromRequest)(
                $this->createStub(ManagerInterface::class),
            )
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInvokeWithoutAccountId(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $result = ($this->loadAccountFromRequest)(
            manager: $manager,
            accountId: null,
        );

        $this->assertInstanceOf(LoadAccountFromRequest::class, $result);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInvokeWithEmptyAccountId(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $result = ($this->loadAccountFromRequest)(
            manager: $manager,
            accountId: '',
        );

        $this->assertInstanceOf(LoadAccountFromRequest::class, $result);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInvokeWithoutAdminRole(): void
    {
        $user = $this->createStub(User::class);
        $user->method('getRoles')
            ->willReturn(['ROLE_USER']);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $result = ($this->loadAccountFromRequest)(
            manager: $manager,
            user: $user,
            accountId: 'account-123',
        );

        $this->assertInstanceOf(LoadAccountFromRequest::class, $result);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInvokeWithNullUser(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $result = ($this->loadAccountFromRequest)(
            manager: $manager,
            user: null,
            accountId: 'account-123',
        );

        $this->assertInstanceOf(LoadAccountFromRequest::class, $result);
    }

    public function testInvokeWithAdminRoleLoadingAccountFromLoader(): void
    {
        $user = $this->createStub(User::class);
        $user->method('getRoles')
            ->willReturn(['ROLE_ADMIN']);

        $account = $this->createStub(Account::class);
        $spaceAccount = $this->createStub(SpaceAccount::class);
        $spaceAccount->method('getId')
            ->willReturn('account-456');
        $spaceAccount->account = $account;

        $this->accountLoader->expects($this->once())
            ->method('load')
            ->willReturnCallback(function ($id, $promise) use ($spaceAccount) {
                $promise->success($spaceAccount);
                return $this->accountLoader;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) use ($spaceAccount, $account) {
                    return
                        (isset($workplan[SpaceAccount::class]) && $workplan[SpaceAccount::class] === $spaceAccount)
                        && (isset($workplan[Account::class]) && $workplan[Account::class] === $account)
                        && (isset($workplan['parameters']) && 'account-456' === $workplan['parameters']['accountId']);
                })
            );

        $result = ($this->loadAccountFromRequest)(
            manager: $manager,
            user: $user,
            accountId: 'account-456',
        );

        $this->assertInstanceOf(LoadAccountFromRequest::class, $result);
    }

    public function testInvokeWithAdminRoleAndAccountAlreadyProvided(): void
    {
        $user = $this->createStub(User::class);
        $user->method('getRoles')
            ->willReturn(['ROLE_ADMIN']);

        $account = $this->createStub(Account::class);
        $spaceAccount = $this->createStub(SpaceAccount::class);
        $spaceAccount->method('getId')
            ->willReturn('account-789');
        $spaceAccount->account = $account;

        // Loader should not be called when account is already provided
        $this->accountLoader->expects($this->never())
            ->method('load');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) use ($spaceAccount, $account) {
                    return
                        (isset($workplan[SpaceAccount::class]) && $workplan[SpaceAccount::class] === $spaceAccount)
                        && (isset($workplan[Account::class]) && $workplan[Account::class] === $account)
                        && (isset($workplan['parameters']) && 'account-789' === $workplan['parameters']['accountId']);
                })
            );

        $result = ($this->loadAccountFromRequest)(
            manager: $manager,
            user: $user,
            account: $spaceAccount,
            accountId: 'account-789',
        );

        $this->assertInstanceOf(LoadAccountFromRequest::class, $result);
    }

    public function testInvokeWithMismatchedAccountId(): void
    {
        $user = $this->createStub(User::class);
        $user->method('getRoles')
            ->willReturn(['ROLE_ADMIN']);

        $account = $this->createStub(Account::class);
        $spaceAccount = $this->createStub(SpaceAccount::class);
        $spaceAccount
            ->method('getId')
            ->willReturn('account-999');
        $spaceAccount->account = $account;

        $this->accountLoader->expects($this->once())
            ->method('load')
            ->willReturnCallback(function ($id, $promise) use ($spaceAccount) {
                $promise->success($spaceAccount);
                return $this->accountLoader;
            });

        $manager = $this->createStub(ManagerInterface::class);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('teknoo.space.error.space_account.account.fetching');
        $this->expectExceptionCode(404);

        ($this->loadAccountFromRequest)(
            manager: $manager,
            user: $user,
            accountId: 'account-123',
        );
    }

    public function testInvokeWithCustomParameters(): void
    {
        $user = $this->createStub(User::class);
        $user->method('getRoles')
            ->willReturn(['ROLE_ADMIN']);

        $account = $this->createStub(Account::class);
        $spaceAccount = $this->createStub(SpaceAccount::class);
        $spaceAccount->method('getId')
            ->willReturn('account-555');
        $spaceAccount->account = $account;

        $this->accountLoader->expects($this->once())
            ->method('load')
            ->willReturnCallback(function ($id, $promise) use ($spaceAccount) {
                $promise->success($spaceAccount);
                return $this->accountLoader;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) use ($spaceAccount, $account) {
                    return
                        (isset($workplan[SpaceAccount::class]) && $workplan[SpaceAccount::class] === $spaceAccount)
                        && (isset($workplan[Account::class]) && $workplan[Account::class] === $account)
                        && (isset($workplan['parameters']) && 'account-555' === $workplan['parameters']['accountId'])
                        && (isset($workplan['parameters']) && 'value' === $workplan['parameters']['custom']);
                })
            );

        $result = ($this->loadAccountFromRequest)(
            manager: $manager,
            user: $user,
            accountId: 'account-555',
            parameters: ['custom' => 'value'],
        );

        $this->assertInstanceOf(LoadAccountFromRequest::class, $result);
    }
}
