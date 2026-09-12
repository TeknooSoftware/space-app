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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Kubernetes\Recipe\Step\Environment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Kubernetes\Client;
use Teknoo\Kubernetes\Model\NamespaceModel;
use Teknoo\Kubernetes\Repository\NamespaceRepository;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\DeleteNamespaceFromResumes;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\DockerComposeCluster;
use Teknoo\Space\Object\Config\Exception\UnsupportedClusterTypeException;
use Teknoo\Space\Object\Config\KubernetesCluster;
use Teknoo\Space\Object\DTO\AccountEnvironmentResume;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Recipe\Step\AccountEnvironment\AbstractDeleteFromResumes;
use Teknoo\Space\Writer\AccountEnvironmentWriter;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(DeleteNamespaceFromResumes::class)]
#[CoversClass(AbstractDeleteFromResumes::class)]
class DeleteNamespaceFromResumesTest extends TestCase
{
    private AccountEnvironmentWriter&Stub $accountEnvironmentWriter;

    private DeleteNamespaceFromResumes $deleteNamespaceFromResumes;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->accountEnvironmentWriter = $this->createStub(AccountEnvironmentWriter::class);

        $this->deleteNamespaceFromResumes = new DeleteNamespaceFromResumes(
            $this->accountEnvironmentWriter,
        );
    }

    private function createWallet(Account $account): AccountWallet
    {
        return new AccountWallet([
            new AccountEnvironment(
                $account,
                'Foo',
                'Prod',
                'foo',
                'foo',
                'foo',
                'foo',
                'foo',
                'foo',
                'foo',
                'foo',
                [],
            )->setId('foo'),
        ]);
    }

    private function createSpaceAccount(Account $account): SpaceAccount
    {
        return new SpaceAccount(
            account: $account,
            environments: [
                new AccountEnvironmentResume(
                    'Foo',
                    'Prod',
                    'foo5',
                )
            ]
        );
    }

    private function createCatalog(NamespaceRepository&MockObject $repository): ClusterCatalog
    {
        $client = $this->createStub(Client::class);
        $client->method('__call')
            ->willReturnCallback(
                fn (string $name): NamespaceRepository => match ($name) {
                    'namespaces' => $repository,
                }
            );

        return new ClusterCatalog(
            [
                'foo' => new KubernetesCluster(
                    name: 'foo',
                    sluggyName: 'foo',
                    type: 'foo',
                    masterAddress: 'foo',
                    storageProvisioner: 'foo',
                    dashboardAddress: 'foo',
                    kubernetesClient: $client,
                    token: 'foo',
                    supportRegistry: true,
                    useHnc: false,
                    isExternal: false,
                ),
            ],
            ['Foo' => 'foo'],
        );
    }

    public function testInvokeWithoutCatalog(): void
    {
        $account = (new Account())->setId('acc-1');

        $this->expectException(RuntimeException::class);

        ($this->deleteNamespaceFromResumes)(
            $this->createWallet($account),
            $this->createSpaceAccount($account),
        );
    }

    public function testInvokeWhenTheNamespaceDoesNotExist(): void
    {
        $account = (new Account())->setId('acc-1');

        $repository = $this->createMock(NamespaceRepository::class);
        $repository->expects($this->once())->method('setLabelSelector')->willReturnSelf();
        $repository->expects($this->once())->method('first')->willReturn(null);
        $repository->expects($this->never())->method('delete');

        $this->assertInstanceOf(
            DeleteNamespaceFromResumes::class,
            ($this->deleteNamespaceFromResumes)(
                $this->createWallet($account),
                $this->createSpaceAccount($account),
                $this->createCatalog($repository),
            ),
        );
    }

    public function testInvokeWhenTheNamespaceIsOwnedByAnotherAccount(): void
    {
        $account = (new Account())->setId('acc-1');

        $repository = $this->createMock(NamespaceRepository::class);
        $repository->expects($this->once())->method('setLabelSelector')->willReturnSelf();
        $repository->expects($this->once())->method('first')->willReturn(
            new NamespaceModel(['metadata' => ['name' => 'foo', 'labels' => ['id' => 'acc-2']]])
        );
        $repository->expects($this->never())->method('delete');

        $this->assertInstanceOf(
            DeleteNamespaceFromResumes::class,
            ($this->deleteNamespaceFromResumes)(
                $this->createWallet($account),
                $this->createSpaceAccount($account),
                $this->createCatalog($repository),
            ),
        );
    }

    public function testInvokeDeletesTheNamespace(): void
    {
        $account = (new Account())->setId('acc-1');

        $model = new NamespaceModel(['metadata' => ['name' => 'foo', 'labels' => ['id' => 'acc-1']]]);
        $repository = $this->createMock(NamespaceRepository::class);
        $repository->expects($this->once())->method('setLabelSelector')->willReturnSelf();
        $repository->expects($this->once())->method('first')->willReturn($model);
        $repository->expects($this->once())->method('delete')->with($model)->willReturn([]);

        $this->assertInstanceOf(
            DeleteNamespaceFromResumes::class,
            ($this->deleteNamespaceFromResumes)(
                $this->createWallet($account),
                $this->createSpaceAccount($account),
                $this->createCatalog($repository),
            ),
        );
    }

    public function testInvokeThrowsOnNonKubernetesCluster(): void
    {
        $account = $this->createStub(Account::class);

        $this->expectException(UnsupportedClusterTypeException::class);

        ($this->deleteNamespaceFromResumes)(
            new AccountWallet([
                new AccountEnvironment(
                    $account,
                    'Foo',
                    'Prod',
                    'foo',
                    'foo',
                    'foo',
                    'foo',
                    'foo',
                    'foo',
                    'foo',
                    'foo',
                    [],
                )->setId('foo'),
            ]),
            new SpaceAccount(
                account: $account,
                environments: [
                    new AccountEnvironmentResume(
                        'Foo',
                        'Prod',
                        'foo5',
                    )
                ]
            ),
            new ClusterCatalog(
                ['foo' => new DockerComposeCluster(
                    name: 'foo',
                    sluggyName: 'foo',
                    type: 'docker-compose',
                    masterAddress: 'ssh://u@h:22',
                    dashboardAddress: 'foo',
                    isExternal: false,
                    clientKey: 'k',
                )],
                ['Foo' => 'foo'],
            ),
        );
    }
}

/**
 * (new AccountEnvironment(
 * $account,
 * 'Foo',
 * 'Prod',
 * 'foo',
 * 'foo',
 * 'foo',
 * 'foo',
 * 'foo',
 * 'foo',
 * 'foo',
 * 'foo',
 * [],
 * )
 * )->setId('foo'),
 */
