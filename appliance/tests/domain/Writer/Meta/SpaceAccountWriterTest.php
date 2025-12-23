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

namespace Teknoo\Space\Tests\Unit\Writer\Meta;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Teknoo\East\Common\Contracts\DBSource\BatchManipulationManagerInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Writer\AccountWriter;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Loader\AccountEnvironmentLoader;
use Teknoo\Space\Loader\AccountHistoryLoader;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountData;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;
use Teknoo\Space\Writer\AccountEnvironmentWriter;
use Teknoo\Space\Writer\AccountDataWriter;
use Teknoo\Space\Writer\AccountHistoryWriter;
use Teknoo\Space\Writer\AccountPersistedVariableWriter;
use Teknoo\Space\Writer\Meta\SpaceAccountWriter;
use Throwable;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * Class SpaceAccountWriterTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SpaceAccountWriter::class)]
class SpaceAccountWriterTest extends TestCase
{
    private SpaceAccountWriter $spaceAccountWriter;

    private AccountWriter&MockObject $accountWriter;

    private AccountDataWriter&MockObject $dataWriter;

    private AccountEnvironmentLoader&MockObject $credentialLoader;

    private AccountHistoryLoader&MockObject $historyLoader;

    private AccountEnvironmentWriter&Stub $credentialWriter;

    private AccountHistoryWriter&Stub $historyWriter;

    private AccountPersistedVariableWriter&MockObject $accountPersistedVariableWriter;

    private BatchManipulationManagerInterface&MockObject $batchManipulationManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->accountWriter = $this->createMock(AccountWriter::class);
        $this->dataWriter = $this->createMock(AccountDataWriter::class);
        $this->credentialLoader = $this->createMock(AccountEnvironmentLoader::class);
        $this->historyLoader = $this->createMock(AccountHistoryLoader::class);
        $this->credentialWriter = $this->createStub(AccountEnvironmentWriter::class);
        $this->historyWriter = $this->createStub(AccountHistoryWriter::class);
        $this->accountPersistedVariableWriter = $this->createMock(AccountPersistedVariableWriter::class);
        $this->batchManipulationManager = $this->createMock(BatchManipulationManagerInterface::class);
        $this->spaceAccountWriter = new SpaceAccountWriter(
            $this->accountWriter,
            $this->dataWriter,
            $this->credentialLoader,
            $this->historyLoader,
            $this->credentialWriter,
            $this->historyWriter,
            $this->accountPersistedVariableWriter,
            $this->batchManipulationManager
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSaveWithWrongObject(): void
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof RuntimeException
                    && str_contains($error->getMessage(), 'is not supported by this writer')
                    && $error->getCode() === 500;
            }));

        $this->assertInstanceOf(
            SpaceAccountWriter::class,
            $this->spaceAccountWriter->save(
                $this->createStub(ObjectInterface::class),
                $promise,
                true,
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSaveWithNullPromise(): void
    {
        $this->accountWriter->expects($this->never())
            ->method('save');

        $this->assertInstanceOf(
            SpaceAccountWriter::class,
            $this->spaceAccountWriter->save(
                $this->createStub(ObjectInterface::class),
                null,
                true,
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSaveWithAccountData(): void
    {
        $account = $this->createStub(Account::class);
        $accountData = $this->createMock(AccountData::class);
        $accountData->expects($this->once())
            ->method('setAccount')
            ->with($account);

        $var1 = $this->createMock(AccountPersistedVariable::class);
        $var1->expects($this->once())
            ->method('getId')
            ->willReturn('id1');
        $var2 = $this->createMock(AccountPersistedVariable::class);
        $var2->expects($this->once())
            ->method('getId')
            ->willReturn('id2');

        $spaceAccount = new SpaceAccount($account, $accountData, [$var1, $var2]);

        $this->accountWriter->expects($this->once())
            ->method('save')
            ->willReturnCallback(
                function ($obj, PromiseInterface $promise, $preferReal) use ($account) {
                    $promise->success($account);
                    return $this->accountWriter;
                }
            );

        $this->dataWriter->expects($this->once())
            ->method('save')
            ->with($accountData, null, true);

        $this->accountPersistedVariableWriter->expects($this->exactly(2))
            ->method('save')
            ->willReturnCallback(function ($var, $promise, $preferReal) {
                $this->assertNull($promise);
                $this->assertTrue($preferReal);
                return $this->accountPersistedVariableWriter;
            });

        $this->batchManipulationManager->expects($this->once())
            ->method('deleteQuery')
            ->with($this->anything(), $this->isInstanceOf(PromiseInterface::class));

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->with($account);

        $this->assertInstanceOf(
            SpaceAccountWriter::class,
            $this->spaceAccountWriter->save(
                $spaceAccount,
                $promise,
                true,
            ),
        );
    }


    #[AllowMockObjectsWithoutExpectations]
    public function testSaveWithAccountError(): void
    {
        $account = $this->createStub(Account::class);
        $spaceAccount = new SpaceAccount($account, null, []);

        $this->accountWriter->expects($this->once())
            ->method('save')
            ->willReturnCallback(
                function ($obj, PromiseInterface $promise) {
                    $promise->fail(new RuntimeException('error', 500));
                    return $this->accountWriter;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof RuntimeException
                    && $error->getMessage() === 'teknoo.space.error.space_account.account.persisting'
                    && $error->getCode() === 500;
            }));

        $this->assertInstanceOf(
            SpaceAccountWriter::class,
            $this->spaceAccountWriter->save(
                $spaceAccount,
                $promise,
                true,
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSaveWithAccountErrorDefaultCode(): void
    {
        $account = $this->createStub(Account::class);
        $spaceAccount = new SpaceAccount($account, null, []);

        $this->accountWriter->expects($this->once())
            ->method('save')
            ->willReturnCallback(
                function ($obj, PromiseInterface $promise) {
                    $promise->fail(new RuntimeException('error', 0));
                    return $this->accountWriter;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof RuntimeException
                    && $error->getMessage() === 'teknoo.space.error.space_account.account.persisting'
                    && $error->getCode() === 500;
            }));

        $this->assertInstanceOf(
            SpaceAccountWriter::class,
            $this->spaceAccountWriter->save(
                $spaceAccount,
                $promise,
                true,
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSaveWithAccountErrorWithoutPromise(): void
    {
        $account = $this->createStub(Account::class);
        $spaceAccount = new SpaceAccount($account, null, []);

        $this->accountWriter->expects($this->once())
            ->method('save')
            ->willReturnCallback(
                function ($obj, PromiseInterface $promise) {
                    $promise->fail(new RuntimeException('error', 500));
                    return $this->accountWriter;
                }
            );

        $this->assertInstanceOf(
            SpaceAccountWriter::class,
            $this->spaceAccountWriter->save(
                $spaceAccount,
                null,
                true,
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testRemoveWithWrongObject(): void
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof RuntimeException
                    && str_contains($error->getMessage(), 'is not supported by this writer')
                    && $error->getCode() === 500;
            }));

        $this->assertInstanceOf(
            SpaceAccountWriter::class,
            $this->spaceAccountWriter->remove(
                $this->createStub(ObjectInterface::class),
                $promise,
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testRemoveWithNullPromise(): void
    {
        $this->accountWriter->expects($this->never())
            ->method('remove');

        $this->assertInstanceOf(
            SpaceAccountWriter::class,
            $this->spaceAccountWriter->remove(
                $this->createStub(ObjectInterface::class),
                null,
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testRemoveWithAccountData(): void
    {
        $account = $this->createStub(Account::class);
        $accountData = $this->createStub(AccountData::class);

        $var1 = $this->createStub(AccountPersistedVariable::class);
        $var2 = $this->createStub(AccountPersistedVariable::class);

        $spaceAccount = new SpaceAccount($account, $accountData, [$var1, $var2]);

        $this->dataWriter->expects($this->once())
            ->method('remove')
            ->willReturnCallback(
                function ($data, PromiseInterface $promise) {
                    $promise->success($data);
                    return $this->dataWriter;
                }
            );

        $this->credentialLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    return $this->credentialLoader;
                }
            );

        $this->historyLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    return $this->historyLoader;
                }
            );

        $this->accountWriter->expects($this->once())
            ->method('remove')
            ->with($account, $this->isInstanceOf(PromiseInterface::class));

        $this->accountPersistedVariableWriter->expects($this->exactly(2))
            ->method('remove')
            ->willReturnCallback(function ($var) {
                return $this->accountPersistedVariableWriter;
            });

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success');

        $this->assertInstanceOf(
            SpaceAccountWriter::class,
            $this->spaceAccountWriter->remove(
                $spaceAccount,
                $promise,
            ),
        );
    }


    #[AllowMockObjectsWithoutExpectations]
    public function testRemoveWithDataError(): void
    {
        $account = $this->createStub(Account::class);
        $accountData = $this->createStub(AccountData::class);
        $spaceAccount = new SpaceAccount($account, $accountData, []);

        $this->dataWriter->expects($this->once())
            ->method('remove')
            ->willReturnCallback(
                function ($data, PromiseInterface $promise) {
                    $promise->fail(new RuntimeException('error', 403));
                    return $this->dataWriter;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof RuntimeException
                    && $error->getMessage() === 'teknoo.space.error.space_account.account.deleting'
                    && $error->getCode() === 403;
            }));

        $this->assertInstanceOf(
            SpaceAccountWriter::class,
            $this->spaceAccountWriter->remove(
                $spaceAccount,
                $promise,
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testRemoveWithDataErrorDefaultCode(): void
    {
        $account = $this->createStub(Account::class);
        $accountData = $this->createStub(AccountData::class);
        $spaceAccount = new SpaceAccount($account, $accountData, []);

        $this->dataWriter->expects($this->once())
            ->method('remove')
            ->willReturnCallback(
                function ($data, PromiseInterface $promise) {
                    $promise->fail(new RuntimeException('error', 0));
                    return $this->dataWriter;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof RuntimeException
                    && $error->getMessage() === 'teknoo.space.error.space_account.account.deleting'
                    && $error->getCode() === 500;
            }));

        $this->assertInstanceOf(
            SpaceAccountWriter::class,
            $this->spaceAccountWriter->remove(
                $spaceAccount,
                $promise,
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testRemoveWithDataErrorWithoutPromise(): void
    {
        $account = $this->createStub(Account::class);
        $accountData = $this->createStub(AccountData::class);
        $spaceAccount = new SpaceAccount($account, $accountData, []);

        $this->dataWriter->expects($this->once())
            ->method('remove')
            ->willReturnCallback(
                function ($data, PromiseInterface $promise) {
                    $promise->fail(new RuntimeException('error', 500));
                    return $this->dataWriter;
                }
            );

        $this->assertInstanceOf(
            SpaceAccountWriter::class,
            $this->spaceAccountWriter->remove(
                $spaceAccount,
                null,
            ),
        );
    }
}
