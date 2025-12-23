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

namespace Teknoo\Space\Tests\Unit\Object\Persisted;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Object\Persisted\AccountRegistry;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * Class AccountRegistryTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountRegistry::class)]
class AccountRegistryTest extends TestCase
{
    private AccountRegistry $accountRegistry;

    private Account&MockObject $account;

    private string $registryNamespace;

    private string $registryUrl;

    private string $registryAccountName;

    private string $registryConfigName;

    private string $registryPassword;

    private string $persistentVolumeClaimName;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->account = $this->createMock(Account::class);
        $this->registryNamespace = '42';
        $this->registryUrl = '42';
        $this->registryAccountName = '42';
        $this->registryConfigName = '42';
        $this->registryPassword = '42';
        $this->persistentVolumeClaimName = '42';
        $this->accountRegistry = new AccountRegistry(
            $this->account,
            $this->registryNamespace,
            $this->registryUrl,
            $this->registryAccountName,
            $this->registryConfigName,
            $this->registryPassword,
            $this->persistentVolumeClaimName,
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetAccount(): void
    {
        $this->assertSame($this->account, $this->accountRegistry->getAccount());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetRegistryNamespace(): void
    {
        $this->assertEquals($this->registryNamespace, $this->accountRegistry->getRegistryNamespace());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetRegistryUrl(): void
    {
        $this->assertEquals($this->registryUrl, $this->accountRegistry->getRegistryUrl());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetRegistryConfigName(): void
    {
        $this->assertEquals($this->registryConfigName, $this->accountRegistry->getRegistryConfigName());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetRegistryAccountName(): void
    {
        $this->assertEquals($this->registryAccountName, $this->accountRegistry->getRegistryAccountName());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetRegistryPassword(): void
    {
        $this->assertEquals($this->registryPassword, $this->accountRegistry->getRegistryPassword());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetPersistentVolumeClaimName(): void
    {
        $this->assertEquals($this->persistentVolumeClaimName, $this->accountRegistry->getPersistentVolumeClaimName());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testUpdateRegistry(): void
    {
        $this->assertInstanceOf(
            AccountRegistry::class,
            $new = $this->accountRegistry->updateRegistry('foo', 'bar', 'baz'),
        );

        $this->assertNotSame(
            $new,
            $this->accountRegistry,
        );

        // Verify updated values
        $this->assertEquals('foo', $new->getRegistryUrl());
        $this->assertEquals('bar', $new->getRegistryAccountName());
        $this->assertEquals('baz', $new->getRegistryPassword());

        // Verify original unchanged
        $this->assertEquals($this->registryUrl, $this->accountRegistry->getRegistryUrl());
        $this->assertEquals($this->registryAccountName, $this->accountRegistry->getRegistryAccountName());
        $this->assertEquals($this->registryPassword, $this->accountRegistry->getRegistryPassword());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testVerifyAccessToUser(): void
    {
        $user = $this->createStub(User::class);
        $promise = $this->createMock(PromiseInterface::class);

        $this->account->expects($this->once())
            ->method('__call')
            ->with('verifyAccessToUser');

        $result = $this->accountRegistry->verifyAccessToUser($user, $promise);

        $this->assertInstanceOf(AccountRegistry::class, $result);
    }
}
