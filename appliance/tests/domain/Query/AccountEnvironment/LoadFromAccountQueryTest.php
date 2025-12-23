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

namespace Teknoo\Space\Tests\Unit\Query\AccountEnvironment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Query\AccountEnvironment\LoadFromAccountQuery;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * Class LoadFromAccountQueryTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(LoadFromAccountQuery::class)]
class LoadFromAccountQueryTest extends TestCase
{
    private LoadFromAccountQuery $loadFromAccountQuery;

    private Account&Stub $account;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->account = $this->createStub(Account::class);
        $this->loadFromAccountQuery = new LoadFromAccountQuery($this->account);
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            LoadFromAccountQuery::class,
            $this->loadFromAccountQuery
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testFetch(): void
    {
        $loader = $this->createStub(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(
                $this->callback(
                    static fn (array $criteria): bool => isset($criteria['account']),
                ),
                $promise
            );

        $this->assertInstanceOf(
            LoadFromAccountQuery::class,
            $this->loadFromAccountQuery->fetch($loader, $repository, $promise)
        );
    }

    public function testExecute(): void
    {
        $loader = $this->createStub(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createStub(PromiseInterface::class);

        $repository->expects($this->once())
            ->method('findBy')
            ->with(
                $this->callback(
                    static fn (array $criteria): bool => isset($criteria['account']),
                ),
                $promise
            );

        $this->assertInstanceOf(
            LoadFromAccountQuery::class,
            $this->loadFromAccountQuery->execute($loader, $repository, $promise)
        );
    }
}
