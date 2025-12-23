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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\AccountRegistry;

use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Loader\AccountRegistryLoader;
use Teknoo\Space\Object\Persisted\AccountRegistry;
use Teknoo\Space\Recipe\Step\AccountRegistry\LoadRegistryCredential;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * Class LoadRegistrysTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(LoadRegistryCredential::class)]
class LoadRegistryCredentialTest extends TestCase
{
    private LoadRegistryCredential $loadRegistryCredential;

    private AccountRegistryLoader&MockObject $loader;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = $this->createMock(AccountRegistryLoader::class);
        $this->loadRegistryCredential = new LoadRegistryCredential($this->loader);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            LoadRegistryCredential::class,
            ($this->loadRegistryCredential)(
                $this->createStub(ManagerInterface::class),
                $this->createStub(Account::class),
                true,
            ),
        );
    }

    public function testInvokeWithAllowEmptyCredentialsAndNullAccount(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');
        $manager->expects($this->never())->method('error');

        $this->loader->expects($this->never())->method('fetch');

        $result = ($this->loadRegistryCredential)(
            manager: $manager,
            accountInstance: null,
            allowEmptyCredentials: true,
        );

        $this->assertInstanceOf(LoadRegistryCredential::class, $result);
    }

    public function testInvokeWithoutAllowEmptyCredentialsAndNullAccount(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($exception) {
                    return $exception instanceof DomainException
                        && 'teknoo.space.error.space_account.account_registry.fetching' === $exception->getMessage()
                        && $exception->getPrevious() instanceof RuntimeException
                        && 'teknoo.space.error.space_account.missing' === $exception->getPrevious()->getMessage();
                })
            );
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->loader->expects($this->never())->method('fetch');

        $result = ($this->loadRegistryCredential)(
            manager: $manager,
            accountInstance: null,
            allowEmptyCredentials: false,
        );

        $this->assertInstanceOf(LoadRegistryCredential::class, $result);
    }

    public function testInvokeWithSuccessfulLoad(): void
    {
        $account = $this->createStub(Account::class);
        $registry = $this->createMock(AccountRegistry::class);
        $registry->expects($this->once())
            ->method('getRegistryNamespace')
            ->willReturn('my-namespace');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) use ($registry) {
                    return isset($workplan[AccountRegistry::class])
                        && $workplan[AccountRegistry::class] === $registry
                        && isset($workplan['registryNamespace'])
                        && 'my-namespace' === $workplan['registryNamespace'];
                })
            );
        $manager->expects($this->never())->method('error');

        $this->loader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($query, $promise) use ($registry) {
                $promise->success($registry);
                return $this->loader;
            });

        $result = ($this->loadRegistryCredential)(
            manager: $manager,
            accountInstance: $account,
            allowEmptyCredentials: false,
        );

        $this->assertInstanceOf(LoadRegistryCredential::class, $result);
    }

    public function testInvokeWithErrorOnLoad(): void
    {
        $account = $this->createStub(Account::class);
        $originalException = new RuntimeException('Fetch failed', 500);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($exception) use ($originalException) {
                    return $exception instanceof DomainException
                        && 'teknoo.space.error.space_account.account_registry.fetching' === $exception->getMessage()
                        && 500 === $exception->getCode()
                        && $exception->getPrevious() === $originalException;
                })
            );
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->loader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($query, $promise) use ($originalException) {
                $promise->fail($originalException);
                return $this->loader;
            });

        $result = ($this->loadRegistryCredential)(
            manager: $manager,
            accountInstance: $account,
            allowEmptyCredentials: false,
        );

        $this->assertInstanceOf(LoadRegistryCredential::class, $result);
    }

    public function testInvokeWithErrorOnLoadAndZeroCode(): void
    {
        $account = $this->createStub(Account::class);
        $originalException = new RuntimeException('Fetch failed', 0);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($exception) use ($originalException) {
                    return $exception instanceof DomainException
                        && 'teknoo.space.error.space_account.account_registry.fetching' === $exception->getMessage()
                        && 404 === $exception->getCode()
                        && $exception->getPrevious() === $originalException;
                })
            );
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->loader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($query, $promise) use ($originalException) {
                $promise->fail($originalException);
                return $this->loader;
            });

        $result = ($this->loadRegistryCredential)(
            manager: $manager,
            accountInstance: $account,
            allowEmptyCredentials: false,
        );

        $this->assertInstanceOf(LoadRegistryCredential::class, $result);
    }

    public function testInvokeWithAllowEmptyCredentialsAndAccount(): void
    {
        $account = $this->createStub(Account::class);
        $registry = $this->createMock(AccountRegistry::class);
        $registry->expects($this->once())
            ->method('getRegistryNamespace')
            ->willReturn('test-namespace');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) use ($registry) {
                    return isset($workplan[AccountRegistry::class])
                        && $workplan[AccountRegistry::class] === $registry
                        && isset($workplan['registryNamespace'])
                        && 'test-namespace' === $workplan['registryNamespace'];
                })
            );

        $this->loader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($query, $promise) use ($registry) {
                $promise->success($registry);
                return $this->loader;
            });

        $result = ($this->loadRegistryCredential)(
            manager: $manager,
            accountInstance: $account,
            allowEmptyCredentials: true,
        );

        $this->assertInstanceOf(LoadRegistryCredential::class, $result);
    }
}
