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

namespace Teknoo\Space\Tests\Unit\Loader\Meta;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Query\QueryCollectionInterface;
use Teknoo\East\Common\Contracts\Query\QueryElementInterface;
use Teknoo\East\Paas\Loader\AccountLoader;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Loader\AccountDataLoader;
use Teknoo\Space\Loader\AccountPersistedVariableLoader;
use Teknoo\Space\Loader\Meta\SpaceAccountLoader;

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

    private AccountLoader|MockObject $accountLoader;

    private AccountDataLoader|MockObject $dataLoader;

    private AccountPersistedVariableLoader|MockObject $accountPersistedVariableLoader;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->accountLoader = $this->createMock(AccountLoader::class);
        $this->dataLoader = $this->createMock(AccountDataLoader::class);
        $this->accountPersistedVariableLoader = $this->createMock(AccountPersistedVariableLoader::class);
        $this->spaceAccountLoader = new SpaceAccountLoader(
            accountLoader: $this->accountLoader,
            dataLoader: $this->dataLoader,
            accountPersistedVariableLoader: $this->accountPersistedVariableLoader
        );
    }

    public function testLoad(): void
    {
        self::assertInstanceOf(
            SpaceAccountLoader::class,
            $this->spaceAccountLoader->load(
                'foo',
                $this->createMock(PromiseInterface::class),
            ),
        );
    }

    public function testQuery(): void
    {
        self::assertInstanceOf(
            SpaceAccountLoader::class,
            $this->spaceAccountLoader->query(
                $this->createMock(QueryCollectionInterface::class),
                $this->createMock(PromiseInterface::class),
            ),
        );
    }

    public function testFetch(): void
    {
        self::assertInstanceOf(
            SpaceAccountLoader::class,
            $this->spaceAccountLoader->fetch(
                $this->createMock(QueryElementInterface::class),
                $this->createMock(PromiseInterface::class),
            ),
        );
    }
}
