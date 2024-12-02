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
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Loader\Meta\SpaceUserLoader;
use Teknoo\Space\Loader\UserDataLoader;

/**
 * Class SpaceUserLoaderTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SpaceUserLoader::class)]
class SpaceUserLoaderTest extends TestCase
{
    private SpaceUserLoader $spaceUserLoader;

    private UserLoader|MockObject $userLoader;

    private UserDataLoader|MockObject $dataLoader;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userLoader = $this->createMock(UserLoader::class);
        $this->dataLoader = $this->createMock(UserDataLoader::class);
        $this->spaceUserLoader = new SpaceUserLoader(
            userLoader: $this->userLoader,
            dataLoader: $this->dataLoader,
        );
    }

    public function testLoad(): void
    {
        self::assertInstanceOf(
            SpaceUserLoader::class,
            $this->spaceUserLoader->load(
                'foo',
                $this->createMock(PromiseInterface::class),
            ),
        );
    }

    public function testQuery(): void
    {
        self::assertInstanceOf(
            SpaceUserLoader::class,
            $this->spaceUserLoader->query(
                $this->createMock(QueryCollectionInterface::class),
                $this->createMock(PromiseInterface::class),
            ),
        );
    }

    public function testFetch(): void
    {
        self::assertInstanceOf(
            SpaceUserLoader::class,
            $this->spaceUserLoader->fetch(
                $this->createMock(QueryElementInterface::class),
                $this->createMock(PromiseInterface::class),
            ),
        );
    }
}
