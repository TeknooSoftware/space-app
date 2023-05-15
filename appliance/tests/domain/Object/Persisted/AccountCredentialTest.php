<?php

/*
 * Teknoo Space.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Object\Persisted;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\Persisted\AccountCredential;

/**
 * Class AccountCredentialTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Object\Persisted\AccountCredential
 */
class AccountCredentialTest extends TestCase
{
    private AccountCredential $accountCredential;

    private Account|MockObject $account;

    private string $registryUrl;

    private string $registryAccountName;

    private string $registryConfigName;

    private string $registryPassword;

    private string $serviceAccountName;

    private string $roleName;

    private string $roleBindingName;

    private string $caCertificate;

    private string $clientCertificate;

    private string $clientKey;

    private string $token;

    private string $persistentVolumeClaimName;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->account = $this->createMock(Account::class);
        $this->registryUrl = '42';
        $this->registryAccountName = '42';
        $this->registryConfigName = '42';
        $this->registryPassword = '42';
        $this->serviceAccountName = '42';
        $this->roleName = '42';
        $this->roleBindingName = '42';
        $this->caCertificate = '42';
        $this->clientCertificate = '42';
        $this->clientKey = '42';
        $this->token = '42';
        $this->persistentVolumeClaimName = '42';
        $this->accountCredential = new AccountCredential(
            $this->account,
            $this->registryUrl,
            $this->registryAccountName,
            $this->registryConfigName,
            $this->registryPassword,
            $this->serviceAccountName,
            $this->roleName,
            $this->roleBindingName,
            $this->caCertificate,
            $this->clientCertificate,
            $this->clientKey,
            $this->token,
            $this->persistentVolumeClaimName,
        );
    }

    public function testGetAccount(): void
    {
        $expected = $this->createMock(Account::class);
        $property = (new ReflectionClass(AccountCredential::class))
            ->getProperty('account');
        $property->setAccessible(true);
        $property->setValue($this->accountCredential, $expected);
        self::assertEquals($expected, $this->accountCredential->getAccount());
    }

    public function testGetRegistryUrl(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountCredential::class))
            ->getProperty('registryUrl');
        $property->setAccessible(true);
        $property->setValue($this->accountCredential, $expected);
        self::assertEquals($expected, $this->accountCredential->getRegistryUrl());
    }

    public function testGetRegistryConfigName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountCredential::class))
            ->getProperty('registryConfigName');
        $property->setAccessible(true);
        $property->setValue($this->accountCredential, $expected);
        self::assertEquals($expected, $this->accountCredential->getRegistryConfigName());
    }

    public function testGetRegistryAccountName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountCredential::class))
            ->getProperty('registryAccountName');
        $property->setAccessible(true);
        $property->setValue($this->accountCredential, $expected);
        self::assertEquals($expected, $this->accountCredential->getRegistryAccountName());
    }

    public function testGetRegistryPassword(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountCredential::class))
            ->getProperty('registryPassword');
        $property->setAccessible(true);
        $property->setValue($this->accountCredential, $expected);
        self::assertEquals($expected, $this->accountCredential->getRegistryPassword());
    }

    public function testGetServiceAccountName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountCredential::class))
            ->getProperty('serviceAccountName');
        $property->setAccessible(true);
        $property->setValue($this->accountCredential, $expected);
        self::assertEquals($expected, $this->accountCredential->getServiceAccountName());
    }

    public function testGetRoleName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountCredential::class))
            ->getProperty('roleName');
        $property->setAccessible(true);
        $property->setValue($this->accountCredential, $expected);
        self::assertEquals($expected, $this->accountCredential->getRoleName());
    }

    public function testGetRoleBindingName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountCredential::class))
            ->getProperty('roleBindingName');
        $property->setAccessible(true);
        $property->setValue($this->accountCredential, $expected);
        self::assertEquals($expected, $this->accountCredential->getRoleBindingName());
    }

    public function testGetCaCertificate(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountCredential::class))
            ->getProperty('caCertificate');
        $property->setAccessible(true);
        $property->setValue($this->accountCredential, $expected);
        self::assertEquals($expected, $this->accountCredential->getCaCertificate());
    }

    public function testGetClientCertificate(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountCredential::class))
            ->getProperty('clientCertificate');
        $property->setAccessible(true);
        $property->setValue($this->accountCredential, $expected);
        self::assertEquals($expected, $this->accountCredential->getClientCertificate());
    }

    public function testGetClientKey(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountCredential::class))
            ->getProperty('clientKey');
        $property->setAccessible(true);
        $property->setValue($this->accountCredential, $expected);
        self::assertEquals($expected, $this->accountCredential->getClientKey());
    }

    public function testGetToken(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountCredential::class))
            ->getProperty('token');
        $property->setAccessible(true);
        $property->setValue($this->accountCredential, $expected);
        self::assertEquals($expected, $this->accountCredential->getToken());
    }

    public function testGetPersistentVolumeClaimName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountCredential::class))
            ->getProperty('persistentVolumeClaimName');
        $property->setAccessible(true);
        $property->setValue($this->accountCredential, $expected);
        self::assertEquals($expected, $this->accountCredential->getPersistentVolumeClaimName());
    }

    public function testUpdateRegistry(): void
    {
        self::assertInstanceOf(
            AccountCredential::class,
            $new = $this->accountCredential->updateRegistry('foo', 'bar', 'foo'),
        );

        self::assertNotSame(
            $new,
            $this->accountCredential,
        );
    }
}
