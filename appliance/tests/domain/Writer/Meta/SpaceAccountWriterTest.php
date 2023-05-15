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

namespace Teknoo\Space\Tests\Unit\Writer\Meta;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\DBSource\BatchManipulationManagerInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Paas\Writer\AccountWriter;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Loader\AccountCredentialLoader;
use Teknoo\Space\Loader\AccountHistoryLoader;
use Teknoo\Space\Writer\AccountCredentialWriter;
use Teknoo\Space\Writer\AccountDataWriter;
use Teknoo\Space\Writer\AccountHistoryWriter;
use Teknoo\Space\Writer\AccountPersistedVariableWriter;
use Teknoo\Space\Writer\Meta\SpaceAccountWriter;

/**
 * Class SpaceAccountWriterTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Writer\Meta\SpaceAccountWriter
 */
class SpaceAccountWriterTest extends TestCase
{
    private SpaceAccountWriter $spaceAccountWriter;

    private AccountWriter|MockObject $accountWriter;

    private AccountDataWriter|MockObject $dataWriter;

    private AccountCredentialLoader|MockObject $credentialLoader;

    private AccountHistoryLoader|MockObject $historyLoader;

    private AccountCredentialWriter|MockObject $credentialWriter;

    private AccountHistoryWriter|MockObject $historyWriter;

    private AccountPersistedVariableWriter|MockObject $accountPersistedVariableWriter;

    private BatchManipulationManagerInterface|MockObject $batchManipulationManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->accountWriter = $this->createMock(AccountWriter::class);
        $this->dataWriter = $this->createMock(AccountDataWriter::class);
        $this->credentialLoader = $this->createMock(AccountCredentialLoader::class);
        $this->historyLoader = $this->createMock(AccountHistoryLoader::class);
        $this->credentialWriter = $this->createMock(AccountCredentialWriter::class);
        $this->historyWriter = $this->createMock(AccountHistoryWriter::class);
        $this->accountPersistedVariableWriter = $this->createMock(AccountPersistedVariableWriter::class);
        $this->batchManipulationManager = $this->createMock(BatchManipulationManagerInterface::class);
        $this->spaceAccountWriter = new SpaceAccountWriter(
            $this->accountWriter,
            $this->dataWriter,
            $this->credentialLoader,
            $this->historyLoader,
            $this->credentialWriter,
            $this->historyWriter,
            $this->accountPersistedVariableWriter,
            $this->batchManipulationManager
        );
    }

    public function testSave(): void
    {
        self::assertInstanceOf(
            SpaceAccountWriter::class,
            $this->spaceAccountWriter->save(
                $this->createMock(ObjectInterface::class),
                $this->createMock(PromiseInterface::class),
                true,
            ),
        );
    }

    public function testRemove(): void
    {
        self::assertInstanceOf(
            SpaceAccountWriter::class,
            $this->spaceAccountWriter->remove(
                $this->createMock(ObjectInterface::class),
                $this->createMock(PromiseInterface::class),
            ),
        );
    }
}
