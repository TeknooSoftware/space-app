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
use Teknoo\Space\Object\Persisted\AccountEnvironment;

/**
 * Class AccountEnvironmentTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountEnvironment::class)]
class AccountEnvironmentTest extends TestCase
{
    private AccountEnvironment $accountEnvironment;

    private Account|MockObject $account;

    private string $clusterName;

    private string $envName;

    private string $namespace;

    private string $serviceAccountName;

    private string $roleName;

    private string $roleBindingName;

    private string $caCertificate;

    private string $clientCertificate;

    private string $clientKey;

    private string $token;

    private array $metadata;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->account = $this->createMock(Account::class);
        $this->clusterName = '42';
        $this->envName = '42';
        $this->namespace = '42';
        $this->serviceAccountName = '42';
        $this->roleName = '42';
        $this->roleBindingName = '42';
        $this->caCertificate = '42';
        $this->clientCertificate = '42';
        $this->clientKey = '42';
        $this->token = '42';
        $this->metadata = ['foo' => 'bar'];
        $this->accountEnvironment = new AccountEnvironment(
            $this->account,
            $this->clusterName,
            $this->envName,
            $this->namespace,
            $this->serviceAccountName,
            $this->roleName,
            $this->roleBindingName,
            $this->caCertificate,
            $this->clientCertificate,
            $this->clientKey,
            $this->token,
            $this->metadata,
        );
    }

    public function testGetAccount(): void
    {
        $expected = $this->createMock(Account::class);
        $property = new ReflectionClass(AccountEnvironment::class)
            ->getProperty('account');
        $property->setValue($this->accountEnvironment, $expected);
        $this->assertEquals($expected, $this->accountEnvironment->getAccount());
    }

    public function testGetServiceAccountName(): void
    {
        $expected = '42';
        $property = new ReflectionClass(AccountEnvironment::class)
            ->getProperty('serviceAccountName');
        $property->setValue($this->accountEnvironment, $expected);
        $this->assertEquals($expected, $this->accountEnvironment->getServiceAccountName());
    }

    public function testGetRoleName(): void
    {
        $expected = '42';
        $property = new ReflectionClass(AccountEnvironment::class)
            ->getProperty('roleName');
        $property->setValue($this->accountEnvironment, $expected);
        $this->assertEquals($expected, $this->accountEnvironment->getRoleName());
    }

    public function testGetRoleBindingName(): void
    {
        $expected = '42';
        $property = new ReflectionClass(AccountEnvironment::class)
            ->getProperty('roleBindingName');
        $property->setValue($this->accountEnvironment, $expected);
        $this->assertEquals($expected, $this->accountEnvironment->getRoleBindingName());
    }

    public function testGetCaCertificate(): void
    {
        $expected = '42';
        $property = new ReflectionClass(AccountEnvironment::class)
            ->getProperty('caCertificate');
        $property->setValue($this->accountEnvironment, $expected);
        $this->assertEquals($expected, $this->accountEnvironment->getCaCertificate());
    }

    public function testGetClientCertificate(): void
    {
        $expected = '42';
        $property = new ReflectionClass(AccountEnvironment::class)
            ->getProperty('clientCertificate');
        $property->setValue($this->accountEnvironment, $expected);
        $this->assertEquals($expected, $this->accountEnvironment->getClientCertificate());
    }

    public function testGetClientKey(): void
    {
        $expected = '42';
        $property = new ReflectionClass(AccountEnvironment::class)
            ->getProperty('clientKey');
        $property->setValue($this->accountEnvironment, $expected);
        $this->assertEquals($expected, $this->accountEnvironment->getClientKey());
    }

    public function testGetToken(): void
    {
        $expected = '42';
        $property = new ReflectionClass(AccountEnvironment::class)
            ->getProperty('token');
        $property->setValue($this->accountEnvironment, $expected);
        $this->assertEquals($expected, $this->accountEnvironment->getToken());
    }

    public function testGetAllMetaData(): void
    {
        $expected = ['foo' => 'bar'];
        $property = new ReflectionClass(AccountEnvironment::class)
            ->getProperty('metadata');
        $property->setValue($this->accountEnvironment, $expected);
        $this->assertEquals($expected, $this->accountEnvironment->getAllMetaData());
        $this->assertEquals('bar', $this->accountEnvironment->getMetaData('foo'));
    }
}
