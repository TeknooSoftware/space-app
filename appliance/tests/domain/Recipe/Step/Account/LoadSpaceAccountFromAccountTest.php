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
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Loader\Meta\SpaceAccountLoader;
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

    private SpaceAccountLoader&MockObject $spaceAccountLoader;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->spaceAccountLoader = $this->createMock(SpaceAccountLoader::class);

        $this->loadSpaceAccountFromAccount = new LoadSpaceAccountFromAccount($this->spaceAccountLoader);
    }

    public function testInvoke(): void
    {
        $account = $this->createMock(Account::class);
        $account->method('getId')->willReturn('fooo');

        $user = $this->createMock(User::class);
        $user->method('getRoles')->willReturn(['ROLE_ADMIN']);

        $spaceAccount = new SpaceAccount($account);

        $this->assertInstanceOf(
            LoadSpaceAccountFromAccount::class,
            ($this->loadSpaceAccountFromAccount)(
                $this->createMock(ManagerInterface::class),
                $account,
                $spaceAccount,
                $user,
            )
        );
    }

    public function testInvokeWithNullAccount(): void
    {
        $user = $this->createMock(User::class);

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

        $this->spaceAccountLoader->expects($this->never())
            ->method('load');

        $result = ($this->loadSpaceAccountFromAccount)(
            manager: $manager,
            accountInstance: null,
            spaceAccount: null,
            user: $user,
        );

        $this->assertInstanceOf(LoadSpaceAccountFromAccount::class, $result);
    }

    public function testInvokeWithNonAdminUser(): void
    {
        $account = $this->createMock(Account::class);

        $user = $this->createMock(User::class);
        $user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_USER']);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($exception) {
                    return $exception instanceof \BadMethodCallException
                        && 'Account is mandatory for non admin user' === $exception->getMessage()
                        && 403 === $exception->getCode();
                })
            );

        $this->spaceAccountLoader->expects($this->never())
            ->method('load');

        $result = ($this->loadSpaceAccountFromAccount)(
            manager: $manager,
            accountInstance: $account,
            spaceAccount: null,
            user: $user,
        );

        $this->assertInstanceOf(LoadSpaceAccountFromAccount::class, $result);
    }

    public function testInvokeWithAdminUserLoadingAccount(): void
    {
        $account = $this->createMock(Account::class);
        $account->expects($this->any())
            ->method('getId')
            ->willReturn('account-123');

        $user = $this->createMock(User::class);
        $user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_ADMIN']);

        $spaceAccount = $this->createMock(SpaceAccount::class);

        $this->spaceAccountLoader->expects($this->once())
            ->method('load')
            ->with('account-123', $this->anything())
            ->willReturnCallback(function ($id, $promise) use ($spaceAccount) {
                $promise->success($spaceAccount);
                return $this->spaceAccountLoader;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) use ($spaceAccount) {
                    return isset($workplan[SpaceAccount::class])
                        && $workplan[SpaceAccount::class] === $spaceAccount;
                })
            );

        $result = ($this->loadSpaceAccountFromAccount)(
            manager: $manager,
            accountInstance: $account,
            spaceAccount: null,
            user: $user,
        );

        $this->assertInstanceOf(LoadSpaceAccountFromAccount::class, $result);
    }

    public function testInvokeWithPromiseFailure(): void
    {
        $account = $this->createMock(Account::class);
        $account->expects($this->any())
            ->method('getId')
            ->willReturn('account-456');

        $user = $this->createMock(User::class);
        $user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_ADMIN']);

        $this->spaceAccountLoader->expects($this->once())
            ->method('load')
            ->with('account-456', $this->anything())
            ->willReturnCallback(function ($id, $promise) {
                $promise->fail(new \RuntimeException('Failed to load account'));
                return $this->spaceAccountLoader;
            });

        $manager = $this->createMock(ManagerInterface::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to load account');

        ($this->loadSpaceAccountFromAccount)(
            manager: $manager,
            accountInstance: $account,
            spaceAccount: null,
            user: $user,
        );
    }

    public function testInvokeWithMatchingSpaceAccountAndAccountInstance(): void
    {
        $account = $this->createMock(Account::class);
        $account->expects($this->any())
            ->method('getId')
            ->willReturn('account-789');

        $accountInSpaceAccount = $this->createMock(Account::class);
        $accountInSpaceAccount->expects($this->any())
            ->method('getId')
            ->willReturn('account-789');

        $spaceAccount = new SpaceAccount($accountInSpaceAccount);

        $user = $this->createMock(User::class);

        // Loader should not be called when IDs match (early return)
        $this->spaceAccountLoader->expects($this->never())
            ->method('load');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('updateWorkPlan');
        $manager->expects($this->never())
            ->method('error');

        $result = ($this->loadSpaceAccountFromAccount)(
            manager: $manager,
            accountInstance: $account,
            spaceAccount: $spaceAccount,
            user: $user,
        );

        $this->assertInstanceOf(LoadSpaceAccountFromAccount::class, $result);
    }
}
