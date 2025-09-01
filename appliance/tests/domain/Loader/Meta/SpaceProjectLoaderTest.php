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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Query\QueryCollectionInterface;
use Teknoo\East\Common\Contracts\Query\QueryElementInterface;
use Teknoo\East\Paas\Loader\ProjectLoader;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Loader\Meta\SpaceProjectLoader;
use Teknoo\Space\Loader\ProjectPersistedVariableLoader;
use Teknoo\Space\Loader\ProjectMetadataLoader;

/**
 * Class SpaceProjectLoaderTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SpaceProjectLoader::class)]
class SpaceProjectLoaderTest extends TestCase
{
    private SpaceProjectLoader $spaceProjectLoader;

    private ProjectLoader&MockObject $projectLoader;

    private ProjectMetadataLoader&MockObject $metadataLoader;

    private ProjectPersistedVariableLoader&MockObject $persistedVariableLoader;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->projectLoader = $this->createMock(ProjectLoader::class);
        $this->metadataLoader = $this->createMock(ProjectMetadataLoader::class);
        $this->persistedVariableLoader = $this->createMock(ProjectPersistedVariableLoader::class);
        $this->spaceProjectLoader = new SpaceProjectLoader(
            projectLoader: $this->projectLoader,
            metadataLoader: $this->metadataLoader,
            persistedVariableLoader: $this->persistedVariableLoader
        );
    }

    public function testLoad(): void
    {
        $this->assertInstanceOf(
            SpaceProjectLoader::class,
            $this->spaceProjectLoader->load(
                'foo',
                $this->createMock(PromiseInterface::class),
            ),
        );
    }

    public function testQuery(): void
    {
        $this->assertInstanceOf(
            SpaceProjectLoader::class,
            $this->spaceProjectLoader->query(
                $this->createMock(QueryCollectionInterface::class),
                $this->createMock(PromiseInterface::class),
            ),
        );
    }

    public function testFetch(): void
    {
        $this->assertInstanceOf(
            SpaceProjectLoader::class,
            $this->spaceProjectLoader->fetch(
                $this->createMock(QueryElementInterface::class),
                $this->createMock(PromiseInterface::class),
            ),
        );
    }
}
