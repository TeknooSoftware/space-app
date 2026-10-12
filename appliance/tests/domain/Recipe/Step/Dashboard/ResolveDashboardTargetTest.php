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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Dashboard;

use BadMethodCallException;
use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Kubernetes\Client;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\ConfigClusterInterface;
use Teknoo\Space\Object\Config\DashboardProfile;
use Teknoo\Space\Object\Config\DashboardProfileCatalog;
use Teknoo\Space\Object\Config\DockerComposeCluster;
use Teknoo\Space\Object\Config\KubernetesCluster;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\DTO\DashboardTarget;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Recipe\Step\Dashboard\ResolveDashboardTarget;
use Teknoo\Space\Service\DashboardAvailability;

/**
 * Class ResolveDashboardTargetTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ResolveDashboardTarget::class)]
class ResolveDashboardTargetTest extends TestCase
{
    private DashboardProfile $headlamp;

    private DashboardProfile $legacy;

    private ResolveDashboardTarget $step;

    private ?DashboardTarget $target = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->headlamp = new DashboardProfile(name: 'headlamp', requestHeaders: [], rewriteBasePath: true);
        $this->legacy = new DashboardProfile(name: 'kubernetes-dashboard', requestHeaders: []);

        $this->step = new ResolveDashboardTarget(
            new DashboardProfileCatalog(
                profiles: ['headlamp' => $this->headlamp, 'kubernetes-dashboard' => $this->legacy],
            ),
            new DashboardAvailability(),
        );
    }

    private function createCluster(
        string $dashboardAddress = 'https://dashboard.test/__headlamp/',
        string $dashboardType = 'headlamp',
    ): KubernetesCluster {
        return new KubernetesCluster(
            name: 'Cluster Name',
            sluggyName: 'cluster-name',
            type: 'kubernetes',
            masterAddress: 'https://kubernetes.test',
            storageProvisioner: 'foo',
            dashboardAddress: $dashboardAddress,
            kubernetesClient: $this->createStub(Client::class),
            token: " cluster-token\n",
            supportRegistry: false,
            useHnc: false,
            isExternal: false,
            dashboardType: $dashboardType,
        );
    }

    private function createDockerComposeCluster(): DockerComposeCluster
    {
        return new DockerComposeCluster(
            name: 'Cluster Name',
            sluggyName: 'cluster-name',
            type: 'docker-compose',
            masterAddress: 'ssh://u@h:22',
            dashboardAddress: 'https://dashboard.test/',
            isExternal: false,
            clientKey: 'k',
            dashboardType: 'kubernetes-dashboard',
        );
    }

    private function createCatalog(?ConfigClusterInterface $cluster = null): ClusterCatalog
    {
        return new ClusterCatalog(
            ['Cluster Name' => $cluster ?? $this->createCluster()],
            ['cluster-name' => 'Cluster Name'],
        );
    }

    private function createManager(): ManagerInterface
    {
        $manager = $this->createStub(ManagerInterface::class);
        $manager->method('updateWorkPlan')
            ->willReturnCallback(function (array $workplan) use ($manager): ManagerInterface {
                $this->target = $workplan[DashboardTarget::class] ?? null;

                return $manager;
            });

        return $manager;
    }

    /**
     * @param array<string> $roles
     */
    private function createUser(array $roles): User
    {
        $user = $this->createStub(User::class);
        $user->method('getRoles')->willReturn($roles);

        return $user;
    }

    private function createWallet(?AccountEnvironment $environment): AccountWallet
    {
        $wallet = $this->createStub(AccountWallet::class);
        $wallet->method('get')
            ->willReturnCallback(
                function (string $cluster, string $env) use ($environment): ?AccountEnvironment {
                    $this->assertSame('Cluster Name', $cluster);
                    $this->assertSame('prod', $env);

                    return $environment;
                }
            );

        return $wallet;
    }

    private function createEnvironment(string $token = 'env-token '): AccountEnvironment
    {
        $environment = $this->createStub(AccountEnvironment::class);
        $environment->method('getToken')->willReturn($token);
        $environment->method('getNamespace')->willReturn('space-client-foo-prod');

        return $environment;
    }

    public function testResolveForAnAdministrator(): void
    {
        $cluster = $this->createCluster();

        $this->assertInstanceOf(
            ResolveDashboardTarget::class,
            ($this->step)(
                manager: $this->createManager(),
                user: $this->createUser(['ROLE_ADMIN']),
                clusterCatalog: $this->createCatalog($cluster),
                clusterName: 'cluster-name',
                envName: '_all',
            ),
        );

        $this->assertInstanceOf(DashboardTarget::class, $this->target);
        $this->assertSame($cluster, $this->target->cluster);
        $this->assertSame($this->headlamp, $this->target->profile);
        $this->assertSame('cluster-token', $this->target->token);
        $this->assertSame('cluster-name', $this->target->clusterName);
        $this->assertSame('_all', $this->target->envName);
        $this->assertNull($this->target->namespace);
    }

    public function testResolveForAUser(): void
    {
        ($this->step)(
            manager: $this->createManager(),
            user: $this->createUser(['ROLE_USER']),
            clusterCatalog: $this->createCatalog(),
            clusterName: 'cluster-name',
            envName: 'prod',
            accountWallet: $this->createWallet($this->createEnvironment()),
        );

        $this->assertInstanceOf(DashboardTarget::class, $this->target);
        $this->assertSame('env-token', $this->target->token);
        $this->assertSame('prod', $this->target->envName);
        $this->assertSame('space-client-foo-prod', $this->target->namespace);
    }

    public function testResolveTheProfileOfTheCluster(): void
    {
        ($this->step)(
            manager: $this->createManager(),
            user: $this->createUser(['ROLE_ADMIN']),
            clusterCatalog: $this->createCatalog(
                $this->createCluster(dashboardType: 'kubernetes-dashboard'),
            ),
            clusterName: 'cluster-name',
        );

        $this->assertSame($this->legacy, $this->target?->profile);
        $this->assertSame('', $this->target?->envName);
    }

    public function testResolveTheDashboardOfAClusterOfAnyType(): void
    {
        $cluster = $this->createDockerComposeCluster();

        ($this->step)(
            manager: $this->createManager(),
            user: $this->createUser(['ROLE_USER']),
            clusterCatalog: $this->createCatalog($cluster),
            clusterName: 'cluster-name',
            envName: 'prod',
            accountWallet: $this->createWallet($this->createEnvironment()),
        );

        $this->assertSame($cluster, $this->target?->cluster);
        $this->assertSame($this->legacy, $this->target?->profile);
        $this->assertSame('env-token', $this->target?->token);
    }

    public function testRefuseAnAdministratorWithoutCredentialForTheDashboard(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionCode(403);

        ($this->step)(
            manager: $this->createManager(),
            user: $this->createUser(['ROLE_ADMIN']),
            clusterCatalog: $this->createCatalog($this->createDockerComposeCluster()),
            clusterName: 'cluster-name',
        );
    }

    public function testRefuseAUserWithoutCredentialForTheDashboard(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionCode(403);

        ($this->step)(
            manager: $this->createManager(),
            user: $this->createUser(['ROLE_USER']),
            clusterCatalog: $this->createCatalog(),
            clusterName: 'cluster-name',
            envName: 'prod',
            accountWallet: $this->createWallet($this->createEnvironment(token: " \n")),
        );
    }

    public function testRefuseAClusterWithoutDashboard(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionCode(404);

        ($this->step)(
            manager: $this->createManager(),
            user: $this->createUser(['ROLE_ADMIN']),
            clusterCatalog: $this->createCatalog($this->createCluster(dashboardAddress: '')),
            clusterName: 'cluster-name',
        );
    }

    public function testRefuseAClusterWithoutDashboardType(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionCode(404);

        ($this->step)(
            manager: $this->createManager(),
            user: $this->createUser(['ROLE_ADMIN']),
            clusterCatalog: $this->createCatalog($this->createCluster(dashboardType: '')),
            clusterName: 'cluster-name',
        );
    }

    public function testRefuseADashboardWithoutTheBasePathRequiredByItsProfile(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionCode(500);

        ($this->step)(
            manager: $this->createManager(),
            user: $this->createUser(['ROLE_ADMIN']),
            clusterCatalog: $this->createCatalog($this->createCluster(dashboardAddress: 'https://dashboard.test/')),
            clusterName: 'cluster-name',
        );
    }

    public function testAcceptARootDashboardWhenItsProfileDoesNotRewriteTheBasePath(): void
    {
        ($this->step)(
            manager: $this->createManager(),
            user: $this->createUser(['ROLE_ADMIN']),
            clusterCatalog: $this->createCatalog(
                $this->createCluster(dashboardAddress: 'https://dashboard.test', dashboardType: 'kubernetes-dashboard'),
            ),
            clusterName: 'cluster-name',
        );

        $this->assertSame($this->legacy, $this->target?->profile);
    }

    public function testRefuseAUserWithoutWallet(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionCode(403);

        ($this->step)(
            manager: $this->createManager(),
            user: $this->createUser(['ROLE_USER']),
            clusterCatalog: $this->createCatalog(),
            clusterName: 'cluster-name',
            envName: 'prod',
        );
    }

    public function testRefuseAUserWithoutEnvironmentName(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionCode(400);

        ($this->step)(
            manager: $this->createManager(),
            user: $this->createUser(['ROLE_USER']),
            clusterCatalog: $this->createCatalog(),
            clusterName: 'cluster-name',
            accountWallet: $this->createWallet($this->createEnvironment()),
        );
    }

    public function testRefuseAUserWithoutEnvironmentOnTheCluster(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionCode(403);

        ($this->step)(
            manager: $this->createManager(),
            user: $this->createUser(['ROLE_USER']),
            clusterCatalog: $this->createCatalog(),
            clusterName: 'cluster-name',
            envName: 'prod',
            accountWallet: $this->createWallet(null),
        );
    }
}
