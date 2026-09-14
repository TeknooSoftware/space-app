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

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Kubernetes\Client;
use Teknoo\Kubernetes\Model\NamespaceModel;
use Teknoo\Kubernetes\Repository\NamespaceRepository;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment\DeleteNamespaces;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\DockerComposeCluster;
use Teknoo\Space\Object\Config\KubernetesCluster;
use Teknoo\Space\Object\Persisted\AccountHistory;

/**
 * Class DeleteNamespacesTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(DeleteNamespaces::class)]
class DeleteNamespacesTest extends TestCase
{
    private const array ENVIRONMENT = [
        'envName' => 'dev',
        'clusterName' => 'Foo',
        'namespace' => 'space-client-foo-dev',
    ];

    private function buildStep(int $expectedHistoryLines): DeleteNamespaces
    {
        $date = new DateTimeImmutable('2026-09-14 10:00:00');

        $datesService = $this->createMock(DatesService::class);
        $datesService->expects($this->exactly($expectedHistoryLines))
            ->method('passMeTheDate')
            ->with($this->isCallable(), true)
            ->willReturnCallback(
                static function (callable $setter) use ($date, $datesService): DatesService {
                    $setter($date);

                    return $datesService;
                }
            );

        return new DeleteNamespaces($datesService, true);
    }

    /**
     * @param array<string> $expectedMessages
     */
    private function buildHistory(array $expectedMessages): AccountHistory&MockObject
    {
        $history = $this->createMock(AccountHistory::class);
        $history->expects($this->exactly(count($expectedMessages)))
            ->method('addToHistory')
            ->with(
                $this->callback(
                    static function (string $message) use (&$expectedMessages): bool {
                        return array_shift($expectedMessages) === $message;
                    }
                ),
                $this->isInstanceOf(DateTimeImmutable::class),
                false,
                self::ENVIRONMENT,
            )
            ->willReturnSelf();

        return $history;
    }

    private function createCatalog(NamespaceRepository&MockObject $repository): ClusterCatalog
    {
        $client = $this->createStub(Client::class);
        $client->method('__call')
            ->willReturnCallback(
                static fn (string $name): NamespaceRepository => match ($name) {
                    'namespaces' => $repository,
                    default => throw new RuntimeException("Unexpected repository $name"),
                }
            );

        return new ClusterCatalog(
            [
                'foo' => new KubernetesCluster(
                    name: 'foo',
                    sluggyName: 'foo',
                    type: 'kubernetes',
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

    public function testInvokeWithoutEnvironmentsDoesNothing(): void
    {
        $repository = $this->createMock(NamespaceRepository::class);
        $repository->expects($this->never())->method('setLabelSelector');

        $this->assertInstanceOf(
            DeleteNamespaces::class,
            $this->buildStep(0)(
                $this->createCatalog($repository),
                [],
                'acc-1',
                $this->buildHistory([]),
            ),
        );
    }

    public function testInvokeWhenTheNamespaceDoesNotExist(): void
    {
        $repository = $this->createMock(NamespaceRepository::class);
        $repository->expects($this->once())->method('setLabelSelector')->willReturnSelf();
        $repository->expects($this->once())->method('first')->willReturn(null);
        $repository->expects($this->never())->method('delete');

        $this->assertInstanceOf(
            DeleteNamespaces::class,
            $this->buildStep(1)(
                $this->createCatalog($repository),
                [self::ENVIRONMENT],
                'acc-1',
                $this->buildHistory(['teknoo.space.text.account.kubernetes.namespace_not_found']),
            ),
        );
    }

    public function testInvokeWhenTheNamespaceIsOwnedByAnotherAccount(): void
    {
        $repository = $this->createMock(NamespaceRepository::class);
        $repository->expects($this->once())->method('setLabelSelector')->willReturnSelf();
        $repository->expects($this->once())->method('first')->willReturn(
            new NamespaceModel(['metadata' => ['name' => 'foo', 'labels' => ['id' => 'acc-2']]])
        );
        $repository->expects($this->never())->method('delete');

        $this->assertInstanceOf(
            DeleteNamespaces::class,
            $this->buildStep(1)(
                $this->createCatalog($repository),
                [self::ENVIRONMENT],
                'acc-1',
                $this->buildHistory(['teknoo.space.text.account.kubernetes.namespace_not_found']),
            ),
        );
    }

    public function testInvokeDeletesTheNamespace(): void
    {
        $model = new NamespaceModel(['metadata' => ['name' => 'foo', 'labels' => ['id' => 'acc-1']]]);
        $repository = $this->createMock(NamespaceRepository::class);
        $repository->expects($this->once())
            ->method('setLabelSelector')
            ->with(['name' => 'space-client-foo-dev'])
            ->willReturnSelf();
        $repository->expects($this->once())->method('first')->willReturn($model);
        $repository->expects($this->once())->method('delete')->with($model)->willReturn([]);

        $this->assertInstanceOf(
            DeleteNamespaces::class,
            $this->buildStep(1)(
                $this->createCatalog($repository),
                [self::ENVIRONMENT],
                'acc-1',
                $this->buildHistory(['teknoo.space.text.account.kubernetes.namespace_deleted']),
            ),
        );
    }

    public function testInvokeOnlyRecordsOnNonKubernetesCluster(): void
    {
        $catalog = new ClusterCatalog(
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
        );

        $this->assertInstanceOf(
            DeleteNamespaces::class,
            $this->buildStep(1)(
                $catalog,
                [self::ENVIRONMENT],
                'acc-1',
                $this->buildHistory(['teknoo.space.text.account.environment.nothing_to_delete']),
            ),
        );
    }
}
