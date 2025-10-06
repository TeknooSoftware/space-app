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

namespace Teknoo\Space\Tests\Unit\Query\AccountPersistedVariable;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\DBSource\QueryExecutorInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Query\AccountPersistedVariable\DeleteVariablesQuery;

/**
 * Class DeleteVariablesQueryTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(DeleteVariablesQuery::class)]
class DeleteVariablesQueryTest extends TestCase
{
    private DeleteVariablesQuery $deleteVariablesQuery;

    private Account&MockObject $account;

    private array $notIds;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->account = $this->createMock(Account::class);
        $this->notIds = [];
        $this->deleteVariablesQuery = new DeleteVariablesQuery($this->account, $this->notIds);
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            DeleteVariablesQuery::class,
            $this->deleteVariablesQuery
        );
    }

    public function testDelete(): void
    {
        $queryExecutor = $this->createMock(QueryExecutorInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $queryExecutor->expects($this->once())
            ->method('filterOn')
            ->with(
                $this->isString(),
                $this->isArray()
            )
            ->willReturnSelf();

        $queryExecutor->expects($this->once())
            ->method('execute')
            ->with($promise);

        $this->assertInstanceOf(
            DeleteVariablesQuery::class,
            $this->deleteVariablesQuery->delete($queryExecutor, $promise)
        );
    }
}
