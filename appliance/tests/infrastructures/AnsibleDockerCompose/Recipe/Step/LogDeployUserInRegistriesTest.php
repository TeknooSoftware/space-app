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

namespace Teknoo\Space\Tests\Unit\Infrastructures\AnsibleDockerCompose\Recipe\Step;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Paas\Object\ClusterCredentials;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Infrastructures\AnsibleDockerCompose\PlaybookRunner;
use Teknoo\Space\Infrastructures\AnsibleDockerCompose\Recipe\Step\LogDeployUserInRegistries;
use Teknoo\Space\Object\Config\ConfigClusterInterface;
use Teknoo\Space\Object\Config\DockerComposeCluster;
use Teknoo\Space\Object\Config\Exception\UnsupportedClusterTypeException;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Object\Persisted\AccountRegistry;

/**
 * Class LogDeployUserInRegistriesTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(LogDeployUserInRegistries::class)]
class LogDeployUserInRegistriesTest extends TestCase
{
    private const string PLAYBOOK = '/path/to/registries-login.yml';

    private const string ADDRESS = 'ssh://deployer@host.example.com:2222';

    private function buildCluster(): DockerComposeCluster
    {
        return new DockerComposeCluster(
            name: 'docker-host',
            sluggyName: 'docker-host',
            type: 'docker-compose',
            masterAddress: self::ADDRESS,
            dashboardAddress: '',
            isExternal: false,
            clientKey: '-----BEGIN OPENSSH PRIVATE KEY-----KEY',
            username: 'deployer',
            caCertificate: 'known-hosts',
            supportRegistry: false,
        );
    }

    private function buildAccountRegistry(): AccountRegistry
    {
        $registry = $this->createStub(AccountRegistry::class);
        $registry->method('getRegistryUrl')->willReturn('my-company.registry.example.com');
        $registry->method('getRegistryAccountName')->willReturn('my-company-registry');
        $registry->method('getRegistryPassword')->willReturn('account-secret');

        return $registry;
    }

    private function buildStep(
        PlaybookRunner $playbookRunner,
        ?DatesService $datesService = null,
        string $spaceRegistryUrl = 'registry.space.example.com',
        string $spaceRegistryUsername = 'space-user',
    ): LogDeployUserInRegistries {
        return new LogDeployUserInRegistries(
            playbookRunner: $playbookRunner,
            playbookPath: self::PLAYBOOK,
            datesService: $datesService ?? $this->createStub(DatesService::class),
            preferRealDate: true,
            spaceRegistryUrl: $spaceRegistryUrl,
            spaceRegistryUsername: $spaceRegistryUsername,
            spaceRegistryPwd: 'space-secret',
        );
    }

    /**
     * @param array<int, mixed> $captured
     */
    private function buildPlaybookRunner(array &$captured, ?RuntimeException $failure = null): PlaybookRunner
    {
        $playbookRunner = $this->createMock(PlaybookRunner::class);
        $playbookRunner->expects($this->once())
            ->method('run')
            ->willReturnCallback(
                static function (
                    string $playbook,
                    string $address,
                    ClusterCredentials $credentials,
                    array $extraVars,
                    PromiseInterface $promise,
                ) use (
                    &$captured,
                    $playbookRunner,
                    $failure,
                ): PlaybookRunner {
                    $captured = [$playbook, $address, $extraVars, $credentials];

                    if (null === $failure) {
                        $promise->success('PLAY RECAP');
                    } else {
                        $promise->fail($failure);
                    }

                    return $playbookRunner;
                }
            );

        return $playbookRunner;
    }

    private function buildDatesService(DateTimeImmutable $date): DatesService
    {
        $datesService = $this->createMock(DatesService::class);
        $datesService->expects($this->once())
            ->method('passMeTheDate')
            ->with($this->isCallable(), true)
            ->willReturnCallback(
                static function (callable $setter) use ($date, $datesService): DatesService {
                    $setter($date);

                    return $datesService;
                }
            );

        return $datesService;
    }

    public function testInvokeThrowsOnNonDockerComposeCluster(): void
    {
        $playbookRunner = $this->createMock(PlaybookRunner::class);
        $playbookRunner->expects($this->never())->method('run');

        $this->expectException(UnsupportedClusterTypeException::class);

        ($this->buildStep($playbookRunner))(
            manager: $this->createStub(ManagerInterface::class),
            clusterConfig: $this->createStub(ConfigClusterInterface::class),
            accountHistory: $this->createStub(AccountHistory::class),
            envName: 'staging',
            clusterName: 'docker-host',
            accountRegistry: $this->buildAccountRegistry(),
        );
    }

    public function testInvokeDoesNothingWithoutAnyRegistry(): void
    {
        $playbookRunner = $this->createMock(PlaybookRunner::class);
        $playbookRunner->expects($this->never())->method('run');

        $accountHistory = $this->createMock(AccountHistory::class);
        $accountHistory->expects($this->never())->method('addToHistory');

        $step = $this->buildStep(playbookRunner: $playbookRunner, spaceRegistryUrl: '');

        $this->assertInstanceOf(
            LogDeployUserInRegistries::class,
            $step(
                manager: $this->createStub(ManagerInterface::class),
                clusterConfig: $this->buildCluster(),
                accountHistory: $accountHistory,
                envName: 'staging',
                clusterName: 'docker-host',
            ),
        );
    }

    public function testInvokeLogsTheDeployUserInOnTheAccountAndSpaceRegistries(): void
    {
        $captured = [];

        $date = new DateTimeImmutable('2026-09-24 18:00:00');
        $accountHistory = $this->createMock(AccountHistory::class);
        $accountHistory->expects($this->once())
            ->method('addToHistory')
            ->with(
                'teknoo.space.text.account.docker_compose.registries_login',
                $date,
                false,
                [
                    'environment' => 'staging',
                    'cluster' => 'docker-host',
                    'registries' => ['my-company.registry.example.com', 'registry.space.example.com'],
                ],
            )
            ->willReturnSelf();

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');

        $step = $this->buildStep($this->buildPlaybookRunner($captured), $this->buildDatesService($date));

        $this->assertInstanceOf(
            LogDeployUserInRegistries::class,
            $step(
                manager: $manager,
                clusterConfig: $this->buildCluster(),
                accountHistory: $accountHistory,
                envName: 'staging',
                clusterName: 'docker-host',
                accountRegistry: $this->buildAccountRegistry(),
            ),
        );

        [$playbook, $address, $extraVars, $credentials] = $captured;
        $this->assertSame(self::PLAYBOOK, $playbook);
        $this->assertSame(self::ADDRESS, $address);
        $this->assertSame(
            [
                'registries' => [
                    [
                        'host' => 'my-company.registry.example.com',
                        'username' => 'my-company-registry',
                        'password' => 'account-secret',
                    ],
                    [
                        'host' => 'registry.space.example.com',
                        'username' => 'space-user',
                        'password' => 'space-secret',
                    ],
                ],
            ],
            $extraVars,
        );
        $this->assertInstanceOf(ClusterCredentials::class, $credentials);
        $this->assertSame('deployer', $credentials->getUsername());
    }

    public function testInvokeSkipsTheSpaceRegistryWithoutUsername(): void
    {
        $captured = [];

        $step = $this->buildStep(
            playbookRunner: $this->buildPlaybookRunner($captured),
            datesService: $this->buildDatesService(new DateTimeImmutable('2026-09-24 18:00:00')),
            spaceRegistryUsername: '',
        );

        $step(
            manager: $this->createStub(ManagerInterface::class),
            clusterConfig: $this->buildCluster(),
            accountHistory: $this->createStub(AccountHistory::class),
            envName: 'staging',
            clusterName: 'docker-host',
            accountRegistry: $this->buildAccountRegistry(),
        );

        $this->assertSame(
            [
                'registries' => [
                    [
                        'host' => 'my-company.registry.example.com',
                        'username' => 'my-company-registry',
                        'password' => 'account-secret',
                    ],
                ],
            ],
            $captured[2],
        );
    }

    public function testInvokeLogsInOnTheSpaceRegistryOnlyWithoutAccountRegistry(): void
    {
        $captured = [];

        $step = $this->buildStep(
            playbookRunner: $this->buildPlaybookRunner($captured),
            datesService: $this->buildDatesService(new DateTimeImmutable('2026-09-24 18:00:00')),
        );

        $step(
            manager: $this->createStub(ManagerInterface::class),
            clusterConfig: $this->buildCluster(),
            accountHistory: $this->createStub(AccountHistory::class),
            envName: 'staging',
            clusterName: 'docker-host',
        );

        $this->assertSame(
            [
                'registries' => [
                    [
                        'host' => 'registry.space.example.com',
                        'username' => 'space-user',
                        'password' => 'space-secret',
                    ],
                ],
            ],
            $captured[2],
        );
    }

    public function testInvokeReportsTheErrorToTheManagerOnFailure(): void
    {
        $error = new RuntimeException('docker login failed');

        $captured = [];

        $datesService = $this->createMock(DatesService::class);
        $datesService->expects($this->never())->method('passMeTheDate');

        $accountHistory = $this->createMock(AccountHistory::class);
        $accountHistory->expects($this->never())->method('addToHistory');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with($error)
            ->willReturnSelf();

        $step = $this->buildStep($this->buildPlaybookRunner($captured, $error), $datesService);

        $this->assertInstanceOf(
            LogDeployUserInRegistries::class,
            $step(
                manager: $manager,
                clusterConfig: $this->buildCluster(),
                accountHistory: $accountHistory,
                envName: 'staging',
                clusterName: 'docker-host',
                accountRegistry: $this->buildAccountRegistry(),
            ),
        );
    }
}
