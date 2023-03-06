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

namespace Teknoo\Space\Tests\Unit\Loader;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\Space\Contracts\DbSource\Repository\ProjectMetadataRepositoryInterface;
use Teknoo\Space\Loader\ProjectMetadataLoader;

/**
 * Class ProjectMetadataLoaderTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license http://teknoo.software/license/mit         MIT License
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Loader\ProjectMetadataLoader
 */
class ProjectMetadataLoaderTest extends TestCase
{
    private ProjectMetadataLoader $projectMetadataLoader;

    private ProjectMetadataRepositoryInterface|MockObject $repository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(ProjectMetadataRepositoryInterface::class);
        $this->projectMetadataLoader = new ProjectMetadataLoader($this->repository);
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(
            ProjectMetadataLoader::class,
            $this->projectMetadataLoader,
        );
    }
}
