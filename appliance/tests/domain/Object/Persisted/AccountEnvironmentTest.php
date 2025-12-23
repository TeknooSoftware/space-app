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
use Teknoo\East\Common\Object\User;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Object\DTO\AccountEnvironmentResume;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

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

    #[AllowMockObjectsWithoutExpectations]
    public function testGetAccount(): void
    {
        $this->assertSame($this->account, $this->accountEnvironment->getAccount());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetClusterName(): void
    {
        $this->assertEquals($this->clusterName, $this->accountEnvironment->getClusterName());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetEnvName(): void
    {
        $this->assertEquals($this->envName, $this->accountEnvironment->getEnvName());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetNamespace(): void
    {
        $this->assertEquals($this->namespace, $this->accountEnvironment->getNamespace());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetServiceAccountName(): void
    {
        $this->assertEquals($this->serviceAccountName, $this->accountEnvironment->getServiceAccountName());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetRoleName(): void
    {
        $this->assertEquals($this->roleName, $this->accountEnvironment->getRoleName());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetRoleBindingName(): void
    {
        $this->assertEquals($this->roleBindingName, $this->accountEnvironment->getRoleBindingName());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetCaCertificate(): void
    {
        $this->assertEquals($this->caCertificate, $this->accountEnvironment->getCaCertificate());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetClientCertificate(): void
    {
        $this->assertEquals($this->clientCertificate, $this->accountEnvironment->getClientCertificate());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetClientKey(): void
    {
        $this->assertEquals($this->clientKey, $this->accountEnvironment->getClientKey());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetToken(): void
    {
        $this->assertEquals($this->token, $this->accountEnvironment->getToken());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetAllMetaData(): void
    {
        $this->assertEquals($this->metadata, $this->accountEnvironment->getAllMetaData());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetAllMetaDataWithNull(): void
    {
        $accountEnvironment = new AccountEnvironment(
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
            null,
        );

        $this->assertNull($accountEnvironment->getAllMetaData());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetMetaDataWithExistingKey(): void
    {
        $this->assertEquals('bar', $this->accountEnvironment->getMetaData('foo'));
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetMetaDataWithMissingKeyAndDefault(): void
    {
        $this->assertEquals('default', $this->accountEnvironment->getMetaData('missing', 'default'));
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetMetaDataWithMissingKeyAndNoDefault(): void
    {
        $this->assertNull($this->accountEnvironment->getMetaData('missing'));
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testVerifyAccessToUser(): void
    {
        $user = $this->createStub(User::class);
        $promise = $this->createMock(PromiseInterface::class);

        $this->account->expects($this->once())
            ->method('__call')
            ->with('verifyAccessToUser');

        $result = $this->accountEnvironment->verifyAccessToUser($user, $promise);

        $this->assertInstanceOf(AccountEnvironment::class, $result);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testResume(): void
    {
        $result = $this->accountEnvironment->resume();

        $this->assertInstanceOf(AccountEnvironmentResume::class, $result);
    }
}
