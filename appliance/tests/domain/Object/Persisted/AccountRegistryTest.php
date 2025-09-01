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
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\Persisted\AccountRegistry;

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

    public function testGetAccount(): void
    {
        $expected = $this->createMock(Account::class);
        $property = new ReflectionClass(AccountRegistry::class)
            ->getProperty('account');
        $property->setValue($this->accountRegistry, $expected);
        $this->assertEquals($expected, $this->accountRegistry->getAccount());
    }

    public function testGetRegistryUrl(): void
    {
        $expected = '42';
        $property = new ReflectionClass(AccountRegistry::class)
            ->getProperty('registryUrl');
        $property->setValue($this->accountRegistry, $expected);
        $this->assertEquals($expected, $this->accountRegistry->getRegistryUrl());
    }

    public function testGetRegistryConfigName(): void
    {
        $expected = '42';
        $property = new ReflectionClass(AccountRegistry::class)
            ->getProperty('registryConfigName');
        $property->setValue($this->accountRegistry, $expected);
        $this->assertEquals($expected, $this->accountRegistry->getRegistryConfigName());
    }

    public function testGetRegistryAccountName(): void
    {
        $expected = '42';
        $property = new ReflectionClass(AccountRegistry::class)
            ->getProperty('registryAccountName');
        $property->setValue($this->accountRegistry, $expected);
        $this->assertEquals($expected, $this->accountRegistry->getRegistryAccountName());
    }

    public function testGetRegistryPassword(): void
    {
        $expected = '42';
        $property = new ReflectionClass(AccountRegistry::class)
            ->getProperty('registryPassword');
        $property->setValue($this->accountRegistry, $expected);
        $this->assertEquals($expected, $this->accountRegistry->getRegistryPassword());
    }

    public function testGetPersistentVolumeClaimName(): void
    {
        $expected = '42';
        $property = new ReflectionClass(AccountRegistry::class)
            ->getProperty('persistentVolumeClaimName');
        $property->setValue($this->accountRegistry, $expected);
        $this->assertEquals($expected, $this->accountRegistry->getPersistentVolumeClaimName());
    }

    public function testUpdateRegistry(): void
    {
        $this->assertInstanceOf(
            AccountRegistry::class,
            $new = $this->accountRegistry->updateRegistry('foo', 'bar', 'foo'),
        );

        $this->assertNotSame(
            $new,
            $this->accountRegistry,
        );
    }
}
