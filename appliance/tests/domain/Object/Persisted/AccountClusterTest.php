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
 * @link        http://https://teknoo.software/applications/space Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Object\Persisted;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\Persisted\AccountCluster;

/**
 * Class AccountClusterTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountCluster::class)]
class AccountClusterTest extends TestCase
{
    private AccountCluster $accountCluster;

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
        $this->accountCluster = new AccountCluster(
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
        $property = (new ReflectionClass(AccountCluster::class))
            ->getProperty('account');
        $property->setAccessible(true);
        $property->setValue($this->accountCluster, $expected);
        self::assertEquals($expected, $this->accountCluster->getAccount());
    }

    public function testGetServiceAccountName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountCluster::class))
            ->getProperty('serviceAccountName');
        $property->setAccessible(true);
        $property->setValue($this->accountCluster, $expected);
        self::assertEquals($expected, $this->accountCluster->getServiceAccountName());
    }

    public function testGetRoleName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountCluster::class))
            ->getProperty('roleName');
        $property->setAccessible(true);
        $property->setValue($this->accountCluster, $expected);
        self::assertEquals($expected, $this->accountCluster->getRoleName());
    }

    public function testGetRoleBindingName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountCluster::class))
            ->getProperty('roleBindingName');
        $property->setAccessible(true);
        $property->setValue($this->accountCluster, $expected);
        self::assertEquals($expected, $this->accountCluster->getRoleBindingName());
    }

    public function testGetCaCertificate(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountCluster::class))
            ->getProperty('caCertificate');
        $property->setAccessible(true);
        $property->setValue($this->accountCluster, $expected);
        self::assertEquals($expected, $this->accountCluster->getCaCertificate());
    }

    public function testGetClientCertificate(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountCluster::class))
            ->getProperty('clientCertificate');
        $property->setAccessible(true);
        $property->setValue($this->accountCluster, $expected);
        self::assertEquals($expected, $this->accountCluster->getClientCertificate());
    }

    public function testGetClientKey(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountCluster::class))
            ->getProperty('clientKey');
        $property->setAccessible(true);
        $property->setValue($this->accountCluster, $expected);
        self::assertEquals($expected, $this->accountCluster->getClientKey());
    }

    public function testGetToken(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountCluster::class))
            ->getProperty('token');
        $property->setAccessible(true);
        $property->setValue($this->accountCluster, $expected);
        self::assertEquals($expected, $this->accountCluster->getToken());
    }

    public function testGetAllMetaData(): void
    {
        $expected = ['foo' => 'bar'];
        $property = (new ReflectionClass(AccountCluster::class))
            ->getProperty('metadata');
        $property->setAccessible(true);
        $property->setValue($this->accountCluster, $expected);
        self::assertEquals($expected, $this->accountCluster->getAllMetaData());
        self::assertEquals('bar', $this->accountCluster->getMetaData('foo'));
    }
}
