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

namespace Teknoo\Space\Tests\Unit\Writer\Meta;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\DBSource\BatchManipulationManagerInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Paas\Writer\ProjectWriter;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Writer\Meta\SpaceProjectWriter;
use Teknoo\Space\Writer\ProjectPersistedVariableWriter;
use Teknoo\Space\Writer\ProjectMetadataWriter;

/**
 * Class SpaceProjectWriterTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SpaceProjectWriter::class)]
class SpaceProjectWriterTest extends TestCase
{
    private SpaceProjectWriter $spaceProjectWriter;

    private ProjectWriter|MockObject $projectWriter;

    private ProjectMetadataWriter|MockObject $metadataWriter;

    private ProjectPersistedVariableWriter|MockObject $persistedVariableWriter;

    private BatchManipulationManagerInterface|MockObject $batchManipulationManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->projectWriter = $this->createMock(ProjectWriter::class);
        $this->metadataWriter = $this->createMock(ProjectMetadataWriter::class);
        $this->persistedVariableWriter = $this->createMock(ProjectPersistedVariableWriter::class);
        $this->batchManipulationManager = $this->createMock(BatchManipulationManagerInterface::class);
        $this->spaceProjectWriter = new SpaceProjectWriter(
            $this->projectWriter,
            $this->metadataWriter,
            $this->persistedVariableWriter,
            $this->batchManipulationManager
        );
    }

    public function testSave(): void
    {
        self::assertInstanceOf(
            SpaceProjectWriter::class,
            $this->spaceProjectWriter->save(
                $this->createMock(ObjectInterface::class),
                $this->createMock(PromiseInterface::class),
                true,
            ),
        );
    }

    public function testRemove(): void
    {
        self::assertInstanceOf(
            SpaceProjectWriter::class,
            $this->spaceProjectWriter->remove(
                $this->createMock(ObjectInterface::class),
                $this->createMock(PromiseInterface::class),
            ),
        );
    }
}
