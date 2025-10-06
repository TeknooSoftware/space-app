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

namespace Teknoo\Space\Tests\Unit\Query\ProjectPersistedVariable;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Query\ProjectPersistedVariable\LoadFromProjectQuery;

/**
 * Class LoadFromProjectQueryTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(LoadFromProjectQuery::class)]
class LoadFromProjectQueryTest extends TestCase
{
    private LoadFromProjectQuery $loadFromProjectQuery;

    private Project&MockObject $project;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->project = $this->createMock(Project::class);
        $this->loadFromProjectQuery = new LoadFromProjectQuery($this->project);
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            LoadFromProjectQuery::class,
            $this->loadFromProjectQuery
        );
    }

    public function testExecute(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $repository->expects($this->once())
            ->method('findBy')
            ->with(
                $this->callback(
                    static fn (array $criteria) => isset($criteria['project']),
                ),
                $promise
            );

        $this->assertInstanceOf(
            LoadFromProjectQuery::class,
            $this->loadFromProjectQuery->execute($loader, $repository, $promise)
        );
    }
}
