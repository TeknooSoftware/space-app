<?php

/*
 * Teknoo Space.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
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
 * @license     https://teknoo.software/license/mit         MIT License
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

    private Account|MockObject $account;

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

    public function testDelete(): void
    {
        self::assertInstanceOf(
            DeleteVariablesQuery::class,
            $this->deleteVariablesQuery->delete(
                $this->createMock(QueryExecutorInterface::class),
                $this->createMock(PromiseInterface::class),
            )
        );
    }
}
