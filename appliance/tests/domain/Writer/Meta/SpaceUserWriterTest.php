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
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Writer\Meta\SpaceUserWriter;
use Teknoo\Space\Writer\UserDataWriter;

/**
 * Class SpaceUserWriterTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SpaceUserWriter::class)]
class SpaceUserWriterTest extends TestCase
{
    private SpaceUserWriter $spaceUserWriter;

    private WriterInterface|MockObject $userWriter;

    private UserDataWriter|MockObject $dataWriter;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userWriter = $this->createMock(WriterInterface::class);
        $this->dataWriter = $this->createMock(UserDataWriter::class);
        $this->spaceUserWriter = new SpaceUserWriter($this->userWriter, $this->dataWriter);
    }

    public function testSave(): void
    {
        self::assertInstanceOf(
            SpaceUserWriter::class,
            $this->spaceUserWriter->save(
                $this->createMock(ObjectInterface::class),
                $this->createMock(PromiseInterface::class),
                true,
            ),
        );
    }

    public function testRemove(): void
    {
        self::assertInstanceOf(
            SpaceUserWriter::class,
            $this->spaceUserWriter->remove(
                $this->createMock(ObjectInterface::class),
                $this->createMock(PromiseInterface::class),
            ),
        );
    }
}
