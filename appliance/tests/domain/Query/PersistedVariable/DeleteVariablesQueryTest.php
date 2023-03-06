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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Query\PersistedVariable;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\DBSource\QueryExecutorInterface;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Query\PersistedVariable\DeleteVariablesQuery;

/**
 * Class DeleteVariablesQueryTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Query\PersistedVariable\DeleteVariablesQuery
 */
class DeleteVariablesQueryTest extends TestCase
{
    private DeleteVariablesQuery $deleteVariablesQuery;

    private Project|MockObject $project;

    private array $notIds;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->project = $this->createMock(Project::class);
        $this->notIds = [];
        $this->deleteVariablesQuery = new DeleteVariablesQuery($this->project, $this->notIds);
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
