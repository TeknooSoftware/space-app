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

namespace Teknoo\Space\Tests\Unit\Loader\Meta;

use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Query\QueryCollectionInterface;
use Teknoo\East\Common\Contracts\Query\QueryElementInterface;
use Teknoo\East\Paas\Loader\AccountLoader;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Loader\AccountDataLoader;
use Teknoo\Space\Loader\AccountPersistedVariableLoader;
use Teknoo\Space\Loader\Meta\SpaceAccountLoader;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountData;
use Throwable;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * Class SpaceAccountLoaderTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SpaceAccountLoader::class)]
class SpaceAccountLoaderTest extends TestCase
{
    private SpaceAccountLoader $spaceAccountLoader;

    private AccountLoader&MockObject $accountLoader;

    private AccountDataLoader&MockObject $dataLoader;

    private AccountPersistedVariableLoader&Stub $accountPersistedVariableLoader;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->accountLoader = $this->createMock(AccountLoader::class);
        $this->dataLoader = $this->createMock(AccountDataLoader::class);
        $this->accountPersistedVariableLoader = $this->createStub(AccountPersistedVariableLoader::class);
        $this->spaceAccountLoader = new SpaceAccountLoader(
            accountLoader: $this->accountLoader,
            dataLoader: $this->dataLoader,
            accountPersistedVariableLoader: $this->accountPersistedVariableLoader
        );
    }

    public function testLoad(): void
    {
        $this->accountLoader->expects($this->once())
            ->method('load')
            ->willReturnCallback(
                function (string $id, PromiseInterface $promise) {
                    $promise->success($this->createStub(Account::class));

                    return $this->accountLoader;
                }
            );

        $this->dataLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->success($this->createStub(AccountData::class));

                    return $this->dataLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->with($this->isInstanceOf(SpaceAccount::class));

        $this->assertInstanceOf(
            SpaceAccountLoader::class,
            $this->spaceAccountLoader->load(
                'foo',
                $promise,
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testLoadWithAccountError(): void
    {
        $this->accountLoader->expects($this->once())
            ->method('load')
            ->willReturnCallback(
                function (string $id, PromiseInterface $promise) {
                    $promise->fail(new DomainException('error', 500));

                    return $this->accountLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof DomainException
                    && $error->getMessage() === 'teknoo.space.error.space_account.account.fetching'
                    && $error->getCode() === 500;
            }));

        $this->assertInstanceOf(
            SpaceAccountLoader::class,
            $this->spaceAccountLoader->load(
                'foo',
                $promise,
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testLoadWithAccountErrorDefaultCode(): void
    {
        $this->accountLoader->expects($this->once())
            ->method('load')
            ->willReturnCallback(
                function (string $id, PromiseInterface $promise) {
                    $promise->fail(new DomainException('error', 0));

                    return $this->accountLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof DomainException
                    && $error->getMessage() === 'teknoo.space.error.space_account.account.fetching'
                    && $error->getCode() === 404;
            }));

        $this->assertInstanceOf(
            SpaceAccountLoader::class,
            $this->spaceAccountLoader->load(
                'foo',
                $promise,
            ),
        );
    }

    public function testLoadWithDataError(): void
    {
        $this->accountLoader->expects($this->once())
            ->method('load')
            ->willReturnCallback(
                function (string $id, PromiseInterface $promise) {
                    $promise->success($this->createStub(Account::class));

                    return $this->accountLoader;
                }
            );

        $this->dataLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->fail(new DomainException('data error'));

                    return $this->dataLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->with($this->callback(function (SpaceAccount $spaceAccount) {
                return $spaceAccount->accountData instanceof AccountData;
            }));

        $this->assertInstanceOf(
            SpaceAccountLoader::class,
            $this->spaceAccountLoader->load(
                'foo',
                $promise,
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testQuery(): void
    {
        $account1 = $this->createStub(Account::class);
        $account2 = $this->createStub(Account::class);

        $this->accountLoader->expects($this->once())
            ->method('query')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) use ($account1, $account2) {
                    $promise->success([$account1, $account2]);

                    return $this->accountLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->with($this->callback(function (array $result) {
                return count($result) === 2
                    && $result[0] instanceof SpaceAccount
                    && $result[1] instanceof SpaceAccount;
            }));

        $this->assertInstanceOf(
            SpaceAccountLoader::class,
            $this->spaceAccountLoader->query(
                $this->createStub(QueryCollectionInterface::class),
                $promise,
            ),
        );
    }

    public function testFetch(): void
    {
        $this->accountLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->success($this->createStub(Account::class));

                    return $this->accountLoader;
                }
            );

        $this->dataLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->success($this->createStub(AccountData::class));

                    return $this->dataLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->with($this->isInstanceOf(SpaceAccount::class));

        $this->assertInstanceOf(
            SpaceAccountLoader::class,
            $this->spaceAccountLoader->fetch(
                $this->createStub(QueryElementInterface::class),
                $promise,
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testFetchWithAccountError(): void
    {
        $this->accountLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->fail(new DomainException('error', 403));

                    return $this->accountLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof DomainException
                    && $error->getMessage() === 'teknoo.space.error.space_account.account_data.fetching'
                    && $error->getCode() === 403;
            }));

        $this->assertInstanceOf(
            SpaceAccountLoader::class,
            $this->spaceAccountLoader->fetch(
                $this->createStub(QueryElementInterface::class),
                $promise,
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testFetchWithAccountErrorDefaultCode(): void
    {
        $this->accountLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->fail(new DomainException('error', 0));

                    return $this->accountLoader;
                }
            );

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(function (Throwable $error) {
                return $error instanceof DomainException
                    && $error->getMessage() === 'teknoo.space.error.space_account.account_data.fetching'
                    && $error->getCode() === 404;
            }));

        $this->assertInstanceOf(
            SpaceAccountLoader::class,
            $this->spaceAccountLoader->fetch(
                $this->createStub(QueryElementInterface::class),
                $promise,
            ),
        );
    }
}
