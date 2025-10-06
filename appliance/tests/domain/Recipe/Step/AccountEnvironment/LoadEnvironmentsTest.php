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

use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Loader\AccountEnvironmentLoader;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Query\AccountEnvironment\LoadFromAccountQuery;
use Teknoo\Space\Recipe\Step\AccountEnvironment\LoadEnvironments;

/**
 * Class loadEnvironmentsTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(LoadEnvironments::class)]
class LoadEnvironmentsTest extends TestCase
{
    private LoadEnvironments $loadEnvironments;

    private AccountEnvironmentLoader&MockObject $loader;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = $this->createMock(AccountEnvironmentLoader::class);
        $this->loadEnvironments = new LoadEnvironments($this->loader);
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            LoadEnvironments::class,
            ($this->loadEnvironments)(
                manager: $this->createMock(ManagerInterface::class),
                accountInstance: $this->createMock(Account::class),
                allowEmptyCredentials: true,
            ),
        );
    }

    public function testInvokeWithSpaceAccount(): void
    {
        $account = $this->createMock(Account::class);
        $spaceAccount = new SpaceAccount($account);

        $this->loader->expects($this->once())
            ->method('query')
            ->with(
                $this->callback(function ($query) use ($account) {
                    return $query instanceof LoadFromAccountQuery;
                }),
                $this->isInstanceOf(PromiseInterface::class)
            );

        $this->assertInstanceOf(
            LoadEnvironments::class,
            ($this->loadEnvironments)(
                manager: $this->createMock(ManagerInterface::class),
                accountInstance: $spaceAccount,
                allowEmptyCredentials: false,
            ),
        );
    }

    public function testInvokeWithAllowEmptyCredentialsAndNullAccount(): void
    {
        $this->loader->expects($this->never())->method('query');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->assertInstanceOf(
            LoadEnvironments::class,
            ($this->loadEnvironments)(
                manager: $manager,
                accountInstance: null,
                allowEmptyCredentials: true,
            ),
        );
    }

    public function testInvokeWithoutAllowEmptyCredentialsAndNullAccount(): void
    {
        $this->loader->expects($this->never())->method('query');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($error) {
                    return $error instanceof DomainException
                        && 'teknoo.space.error.space_account.account_environment.fetching' === $error->getMessage();
                })
            );

        $this->assertInstanceOf(
            LoadEnvironments::class,
            ($this->loadEnvironments)(
                manager: $manager,
                accountInstance: null,
                allowEmptyCredentials: false,
            ),
        );
    }

    public function testInvokeWithSuccessPromise(): void
    {
        $account = $this->createMock(Account::class);
        $env1 = $this->createMock(AccountEnvironment::class);
        $env2 = $this->createMock(AccountEnvironment::class);
        $environments = [$env1, $env2];

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan[AccountWallet::class])
                        && $workplan[AccountWallet::class] instanceof AccountWallet;
                })
            );

        $this->loader->expects($this->once())
            ->method('query')
            ->willReturnCallback(
                function ($query, $promise) use ($environments) {
                    $promise->success($environments);

                    return $this->loader;
                }
            );

        $this->assertInstanceOf(
            LoadEnvironments::class,
            ($this->loadEnvironments)(
                manager: $manager,
                accountInstance: $account,
                allowEmptyCredentials: false,
            ),
        );
    }

    public function testInvokeWithErrorPromiseAndAllowEmptyCredentials(): void
    {
        $account = $this->createMock(Account::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan[AccountWallet::class])
                        && $workplan[AccountWallet::class] instanceof AccountWallet;
                })
            );
        $manager->expects($this->never())->method('error');

        $this->loader->expects($this->once())
            ->method('query')
            ->willReturnCallback(
                function ($query, $promise) {
                    $promise->fail(new RuntimeException('Test error'));

                    return $this->loader;
                }
            );

        $this->assertInstanceOf(
            LoadEnvironments::class,
            ($this->loadEnvironments)(
                manager: $manager,
                accountInstance: $account,
                allowEmptyCredentials: true,
            ),
        );
    }

    public function testInvokeWithErrorPromiseAndNoAllowEmptyCredentials(): void
    {
        $account = $this->createMock(Account::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');
        $manager->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($error) {
                    return $error instanceof DomainException
                        && 'teknoo.space.error.space_account.account_environment.fetching' === $error->getMessage()
                        && 404 === $error->getCode()
                        && $error->getPrevious() instanceof RuntimeException;
                })
            );

        $this->loader->expects($this->once())
            ->method('query')
            ->willReturnCallback(
                function ($query, $promise) {
                    $promise->fail(new RuntimeException('Test error'));

                    return $this->loader;
                }
            );

        $this->assertInstanceOf(
            LoadEnvironments::class,
            ($this->loadEnvironments)(
                manager: $manager,
                accountInstance: $account,
                allowEmptyCredentials: false,
            ),
        );
    }

    public function testInvokeWithErrorPromiseWithErrorCode(): void
    {
        $account = $this->createMock(Account::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($error) {
                    return $error instanceof DomainException
                        && 500 === $error->getCode();
                })
            );

        $this->loader->expects($this->once())
            ->method('query')
            ->willReturnCallback(
                function ($query, $promise) {
                    $promise->fail(new RuntimeException('Test error', 500));

                    return $this->loader;
                }
            );

        $this->assertInstanceOf(
            LoadEnvironments::class,
            ($this->loadEnvironments)(
                manager: $manager,
                accountInstance: $account,
                allowEmptyCredentials: false,
            ),
        );
    }

    public function testInvokeWithEmptyCredentialsArray(): void
    {
        $account = $this->createMock(Account::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan[AccountWallet::class])
                        && $workplan[AccountWallet::class] instanceof AccountWallet;
                })
            );

        $this->loader->expects($this->once())
            ->method('query')
            ->willReturnCallback(
                function ($query, $promise) {
                    $promise->success([]);

                    return $this->loader;
                }
            );

        $this->assertInstanceOf(
            LoadEnvironments::class,
            ($this->loadEnvironments)(
                manager: $manager,
                accountInstance: $account,
                allowEmptyCredentials: true,
            ),
        );
    }
}
