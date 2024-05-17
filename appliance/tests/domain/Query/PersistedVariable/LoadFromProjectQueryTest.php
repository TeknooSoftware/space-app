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
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Query\PersistedVariable;

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
 * @covers \Teknoo\Space\Query\ProjectPersistedVariable\LoadFromProjectQuery
 */
class LoadFromProjectQueryTest extends TestCase
{
    private LoadFromProjectQuery $loadFromProjectQuery;

    private Project|MockObject $project;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->project = $this->createMock(Project::class);
        $this->loadFromProjectQuery = new LoadFromProjectQuery($this->project);
    }

    public function testExecute(): void
    {
        self::assertInstanceOf(
            LoadFromProjectQuery::class,
            $this->loadFromProjectQuery->execute(
                $this->createMock(LoaderInterface::class),
                $this->createMock(RepositoryInterface::class),
                $this->createMock(PromiseInterface::class),
            )
        );
    }
}
