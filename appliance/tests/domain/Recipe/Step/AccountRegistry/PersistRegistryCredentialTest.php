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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\AccountRegistry;

use DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Object\Persisted\AccountRegistry;
use Teknoo\Space\Recipe\Step\AccountRegistry\PersistRegistryCredential;
use Teknoo\Space\Writer\AccountRegistryWriter;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * Class PersistRegistrysTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(PersistRegistryCredential::class)]
class PersistRegistryCredentialTest extends TestCase
{
    private PersistRegistryCredential $persistRegistryCredential;

    private AccountRegistryWriter&MockObject $writer;

    private DatesService&MockObject $datesService;

    private bool $preferRealDate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->writer = $this->createMock(AccountRegistryWriter::class);
        $this->datesService = $this->createMock(DatesService::class);
        $this->preferRealDate = true;
        $this->persistRegistryCredential = new PersistRegistryCredential(
            $this->writer,
            $this->datesService,
            $this->preferRealDate,
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            PersistRegistryCredential::class,
            ($this->persistRegistryCredential)(
                manager: $this->createStub(ManagerInterface::class),
                object: $this->createStub(ObjectInterface::class),
                kubeNamespace: 'foo',
                registryUrl: 'foo',
                registryAccountName: 'foo',
                registryConfigName: 'foo',
                registryPassword: 'foo',
                persistentVolumeClaimName: 'foo',
                accountHistory: $this->createStub(AccountHistory::class),
            ),
        );
    }

    /**
     * The registry is bound to the cluster it was installed on: the resolved cluster name must be carried by the
     * persisted object so a later reinstall targets that same cluster.
     */
    public function testInvokeRecordsTheResolvedRegistryClusterName(): void
    {
        $account = $this->createStub(Account::class);

        $saved = null;
        $this->writer->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (AccountRegistry $registry) use (&$saved): AccountRegistryWriter {
                $saved = $registry;

                return $this->writer;
            });

        $this->datesService->expects($this->once())
            ->method('passMeTheDate')
            ->willReturnCallback(function ($callback): DatesService {
                $callback(new DateTime('2026-09-16'));

                return $this->datesService;
            });

        ($this->persistRegistryCredential)(
            manager: $this->createStub(ManagerInterface::class),
            object: $account,
            kubeNamespace: 'test-namespace',
            registryUrl: 'https://registry.example.com',
            registryAccountName: 'user123',
            registryConfigName: 'config123',
            registryPassword: 'secret',
            persistentVolumeClaimName: 'pvc-123',
            accountHistory: $this->createStub(AccountHistory::class),
            registryClusterName: 'Registry Cluster',
        );

        $this->assertInstanceOf(AccountRegistry::class, $saved);
        $this->assertSame('Registry Cluster', $saved->getClusterName());
    }

    /**
     * Without a resolved cluster the registry stays "legacy": the name is null and the next reinstall falls back
     * to the first cluster supporting the registry, then records it.
     */
    public function testInvokeWithoutARegistryClusterNameKeepsItNull(): void
    {
        $account = $this->createStub(Account::class);

        $saved = null;
        $this->writer->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (AccountRegistry $registry) use (&$saved): AccountRegistryWriter {
                $saved = $registry;

                return $this->writer;
            });

        $this->datesService->expects($this->once())
            ->method('passMeTheDate')
            ->willReturnCallback(function ($callback): DatesService {
                $callback(new DateTime('2026-09-16'));

                return $this->datesService;
            });

        ($this->persistRegistryCredential)(
            manager: $this->createStub(ManagerInterface::class),
            object: $account,
            kubeNamespace: 'test-namespace',
            registryUrl: 'https://registry.example.com',
            registryAccountName: 'user123',
            registryConfigName: 'config123',
            registryPassword: 'secret',
            persistentVolumeClaimName: 'pvc-123',
            accountHistory: $this->createStub(AccountHistory::class),
        );

        $this->assertInstanceOf(AccountRegistry::class, $saved);
        $this->assertNull($saved->getClusterName());
    }

    public function testInvokeWithNonAccountObject(): void
    {
        $object = $this->createStub(ObjectInterface::class);
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');
        $manager->expects($this->never())->method('cleanWorkPlan');

        $this->writer->expects($this->never())->method('save');
        $this->datesService->expects($this->never())->method('passMeTheDate');

        $result = ($this->persistRegistryCredential)(
            manager: $manager,
            object: $object,
            kubeNamespace: 'test-namespace',
            registryUrl: 'https://registry.example.com',
            registryAccountName: 'user123',
            registryConfigName: 'config123',
            registryPassword: 'secret',
            persistentVolumeClaimName: 'pvc-123',
            accountHistory: $this->createStub(AccountHistory::class),
        );

        $this->assertInstanceOf(PersistRegistryCredential::class, $result);
    }

    public function testInvokeWithAccountObject(): void
    {
        $account = $this->createStub(Account::class);
        $accountHistory = $this->createMock(AccountHistory::class);
        $dateTime = new DateTime('2025-10-02');

        $accountHistory->expects($this->once())
            ->method('addToHistory')
            ->with(
                'teknoo.space.text.account.kubernetes.registry_persisted',
                $dateTime
            );

        $this->writer->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(AccountRegistry::class))
            ->willReturnSelf();

        $this->datesService->expects($this->once())
            ->method('passMeTheDate')
            ->willReturnCallback(function ($callback, $preferRealDate) use ($dateTime) {
                $this->assertTrue($preferRealDate);
                $callback($dateTime);
                return $this->datesService;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan[AccountRegistry::class])
                        && $workplan[AccountRegistry::class] instanceof AccountRegistry;
                })
            );
        $manager->expects($this->once())
            ->method('cleanWorkPlan')
            ->with('registryAccountName', 'registryPassword');

        $result = ($this->persistRegistryCredential)(
            manager: $manager,
            object: $account,
            kubeNamespace: 'test-namespace',
            registryUrl: 'https://registry.example.com',
            registryAccountName: 'user123',
            registryConfigName: 'config123',
            registryPassword: 'secret',
            persistentVolumeClaimName: 'pvc-123',
            accountHistory: $accountHistory,
        );

        $this->assertInstanceOf(PersistRegistryCredential::class, $result);
    }

    public function testInvokeWithSpaceAccountObject(): void
    {
        $account = $this->createStub(Account::class);
        $spaceAccount = $this->createStub(SpaceAccount::class);
        $spaceAccount->account = $account;
        $accountHistory = $this->createMock(AccountHistory::class);
        $dateTime = new DateTime('2025-10-02');

        $accountHistory->expects($this->once())
            ->method('addToHistory')
            ->with(
                'teknoo.space.text.account.kubernetes.registry_persisted',
                $dateTime
            );

        $this->writer->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(AccountRegistry::class))
            ->willReturnSelf();

        $this->datesService->expects($this->once())
            ->method('passMeTheDate')
            ->willReturnCallback(function ($callback, $preferRealDate) use ($dateTime) {
                $this->assertTrue($preferRealDate);
                $callback($dateTime);
                return $this->datesService;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan[AccountRegistry::class])
                        && $workplan[AccountRegistry::class] instanceof AccountRegistry;
                })
            );
        $manager->expects($this->once())
            ->method('cleanWorkPlan')
            ->with('registryAccountName', 'registryPassword');

        $result = ($this->persistRegistryCredential)(
            manager: $manager,
            object: $spaceAccount,
            kubeNamespace: 'test-namespace',
            registryUrl: 'https://registry.example.com',
            registryAccountName: 'user123',
            registryConfigName: 'config123',
            registryPassword: 'secret',
            persistentVolumeClaimName: 'pvc-123',
            accountHistory: $accountHistory,
        );

        $this->assertInstanceOf(PersistRegistryCredential::class, $result);
    }

    public function testInvokeWithPreferRealDateFalse(): void
    {
        $this->persistRegistryCredential = new PersistRegistryCredential(
            $this->writer,
            $this->datesService,
            false,
        );

        $account = $this->createStub(Account::class);
        $accountHistory = $this->createMock(AccountHistory::class);
        $dateTime = new DateTime('2025-10-02');

        $accountHistory->expects($this->once())
            ->method('addToHistory')
            ->with(
                'teknoo.space.text.account.kubernetes.registry_persisted',
                $dateTime
            );

        $this->writer->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(AccountRegistry::class))
            ->willReturnSelf();

        $this->datesService->expects($this->once())
            ->method('passMeTheDate')
            ->willReturnCallback(function ($callback, $preferRealDate) use ($dateTime) {
                $this->assertFalse($preferRealDate);
                $callback($dateTime);
                return $this->datesService;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan[AccountRegistry::class])
                        && $workplan[AccountRegistry::class] instanceof AccountRegistry;
                })
            );
        $manager->expects($this->once())
            ->method('cleanWorkPlan')
            ->with('registryAccountName', 'registryPassword');

        $result = ($this->persistRegistryCredential)(
            manager: $manager,
            object: $account,
            kubeNamespace: 'test-namespace',
            registryUrl: 'https://registry.example.com',
            registryAccountName: 'user123',
            registryConfigName: 'config123',
            registryPassword: 'secret',
            persistentVolumeClaimName: 'pvc-123',
            accountHistory: $accountHistory,
        );

        $this->assertInstanceOf(PersistRegistryCredential::class, $result);
    }
}
