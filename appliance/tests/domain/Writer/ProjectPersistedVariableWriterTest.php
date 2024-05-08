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

namespace Teknoo\Space\Tests\Unit\Writer;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Service\PersistedVariableEncryption;
use Teknoo\Space\Writer\ProjectPersistedVariableWriter;

/**
 * Class ProjectPersistedVariableWriterTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Writer\PersistedVariableWriterTrait
 * @covers \Teknoo\Space\Writer\ProjectPersistedVariableWriter
 */
class ProjectPersistedVariableWriterTest extends TestCase
{
    private ProjectPersistedVariableWriter $persistedVariableWriter;

    private ManagerInterface|MockObject $manager;

    private PersistedVariableEncryption|MockObject $persistedVariableEncryption;

    private DatesService|MockObject $datesService;

    protected bool $preferRealDateOnUpdate = false;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->createMock(ManagerInterface::class);
        $this->persistedVariableEncryption = $this->createMock(PersistedVariableEncryption::class);
        $this->datesService = $this->createMock(DatesService::class);
        $this->preferRealDateOnUpdate = true;

        $this->persistedVariableWriter = new ProjectPersistedVariableWriter(
            $this->manager,
            $this->persistedVariableEncryption,
            $this->datesService,
            $this->preferRealDateOnUpdate,
        );
    }

    public function testSave(): void
    {
        self::assertInstanceOf(
            ProjectPersistedVariableWriter::class,
            $this->persistedVariableWriter->save(
                $this->createMock(ObjectInterface::class),
                $this->createMock(PromiseInterface::class),
                true,
            ),
        );
    }
}