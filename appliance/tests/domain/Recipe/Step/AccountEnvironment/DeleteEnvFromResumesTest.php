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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\Config\Cluster;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\AccountEnvironmentResume;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Recipe\Step\AccountEnvironment\AbstractDeleteFromResumes;
use Teknoo\Space\Recipe\Step\AccountEnvironment\DeleteEnvFromResumes;
use Teknoo\Space\Writer\AccountEnvironmentWriter;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(DeleteEnvFromResumes::class)]
#[CoversClass(AbstractDeleteFromResumes::class)]
class DeleteEnvFromResumesTest extends TestCase
{
    private AccountEnvironmentWriter&MockObject $accountEnvironmentWriter;
    private DeleteEnvFromResumes $deleteEnvFromResumes;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->accountEnvironmentWriter = $this->createMock(AccountEnvironmentWriter::class);

        $this->deleteEnvFromResumes = new DeleteEnvFromResumes(
            $this->accountEnvironmentWriter,
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            DeleteEnvFromResumes::class,
            ($this->deleteEnvFromResumes)(
                new AccountWallet([$this->createStub(AccountEnvironment::class)]),
                new SpaceAccount(
                    account: $this->createStub(Account::class),
                    environments: []
                ),
                new ClusterCatalog(['Foo' => $this->createStub(Cluster::class)], ['Foo' => 'foo']),
            ),
        );
    }

    public function testInvokeWithNullEnvironments(): void
    {
        $this->accountEnvironmentWriter->expects($this->never())->method('remove');

        $this->assertInstanceOf(
            DeleteEnvFromResumes::class,
            ($this->deleteEnvFromResumes)(
                new AccountWallet([$this->createStub(AccountEnvironment::class)]),
                new SpaceAccount(
                    account: $this->createStub(Account::class),
                    environments: null
                ),
            ),
        );
    }

    public function testInvokeWithEnvironmentToDelete(): void
    {
        $env = $this->createStub(AccountEnvironment::class);
        $env->method('getId')
            ->willReturn('env-123');

        $this->accountEnvironmentWriter->expects($this->once())
            ->method('remove')
            ->with($env);

        $resume = new AccountEnvironmentResume(
            accountEnvironmentId: 'env-456',
            clusterName: 'cluster1',
            envName: 'env1',
        );

        $this->assertInstanceOf(
            DeleteEnvFromResumes::class,
            ($this->deleteEnvFromResumes)(
                new AccountWallet([$env]),
                new SpaceAccount(
                    account: $this->createStub(Account::class),
                    environments: [$resume]
                ),
            ),
        );
    }

    public function testInvokeWithEnvironmentInResumes(): void
    {
        $env = $this->createStub(AccountEnvironment::class);
        $env->method('getId')
            ->willReturn('env-123');

        $this->accountEnvironmentWriter->expects($this->never())->method('remove');

        $resume = new AccountEnvironmentResume(
            accountEnvironmentId: 'env-123',
            clusterName: 'cluster1',
            envName: 'env1',
        );

        $this->assertInstanceOf(
            DeleteEnvFromResumes::class,
            ($this->deleteEnvFromResumes)(
                new AccountWallet([$env]),
                new SpaceAccount(
                    account: $this->createStub(Account::class),
                    environments: [$resume]
                ),
            ),
        );
    }

    public function testInvokeWithEmptyEnvironmentId(): void
    {
        $env = $this->createStub(AccountEnvironment::class);
        $env->method('getId')
            ->willReturn('');

        $this->accountEnvironmentWriter->expects($this->never())->method('remove');

        $resume = new AccountEnvironmentResume(
            accountEnvironmentId: 'env-123',
            clusterName: 'cluster1',
            envName: 'env1',
        );

        $this->assertInstanceOf(
            DeleteEnvFromResumes::class,
            ($this->deleteEnvFromResumes)(
                new AccountWallet([$env]),
                new SpaceAccount(
                    account: $this->createStub(Account::class),
                    environments: [$resume]
                ),
            ),
        );
    }

    public function testInvokeWithEmptyResumeId(): void
    {
        $env = $this->createStub(AccountEnvironment::class);
        $env->method('getId')
            ->willReturn('env-123');

        $this->accountEnvironmentWriter->expects($this->once())
            ->method('remove')
            ->with($env);

        $resume = new AccountEnvironmentResume(
            accountEnvironmentId: '',
            clusterName: 'cluster1',
            envName: 'env1',
        );

        $this->assertInstanceOf(
            DeleteEnvFromResumes::class,
            ($this->deleteEnvFromResumes)(
                new AccountWallet([$env]),
                new SpaceAccount(
                    account: $this->createStub(Account::class),
                    environments: [$resume]
                ),
            ),
        );
    }

    public function testInvokeWithMultipleEnvironments(): void
    {
        $env1 = $this->createStub(AccountEnvironment::class);
        $env1->method('getId')
            ->willReturn('env-1');

        $env2 = $this->createStub(AccountEnvironment::class);
        $env2->method('getId')
            ->willReturn('env-2');

        $env3 = $this->createStub(AccountEnvironment::class);
        $env3->method('getId')
            ->willReturn('env-3');

        // Only env2 should be removed (not in resumes)
        $this->accountEnvironmentWriter->expects($this->once())
            ->method('remove')
            ->with($env2);

        $resume1 = new AccountEnvironmentResume(
            accountEnvironmentId: 'env-1',
            clusterName: 'cluster1',
            envName: 'env1',
        );

        $resume3 = new AccountEnvironmentResume(
            accountEnvironmentId: 'env-3',
            clusterName: 'cluster2',
            envName: 'env3',
        );

        $this->assertInstanceOf(
            DeleteEnvFromResumes::class,
            ($this->deleteEnvFromResumes)(
                new AccountWallet([$env1, $env2, $env3]),
                new SpaceAccount(
                    account: $this->createStub(Account::class),
                    environments: [$resume1, $resume3]
                ),
            ),
        );
    }

    public function testInvokeWithNullClusterCatalog(): void
    {
        $env = $this->createStub(AccountEnvironment::class);
        $env->method('getId')
            ->willReturn('env-123');

        $this->accountEnvironmentWriter->expects($this->once())
            ->method('remove')
            ->with($env);

        $resume = new AccountEnvironmentResume(
            accountEnvironmentId: 'env-456',
            clusterName: 'cluster1',
            envName: 'env1',
        );

        $this->assertInstanceOf(
            DeleteEnvFromResumes::class,
            ($this->deleteEnvFromResumes)(
                new AccountWallet([$env]),
                new SpaceAccount(
                    account: $this->createStub(Account::class),
                    environments: [$resume]
                ),
                null,
            ),
        );
    }
}
