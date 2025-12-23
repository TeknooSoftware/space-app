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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Account;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Recipe\Step\Account\UpdateAccountHistory;
use Teknoo\Space\Writer\AccountHistoryWriter;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * Class UpdateAccountHistoryTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(UpdateAccountHistory::class)]
class UpdateAccountHistoryTest extends TestCase
{
    private UpdateAccountHistory $updateAccountHistory;

    private AccountHistoryWriter&MockObject $writer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->writer = $this->createMock(AccountHistoryWriter::class);
        $this->updateAccountHistory = new UpdateAccountHistory($this->writer);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            UpdateAccountHistory::class,
            ($this->updateAccountHistory)(
                $this->createStub(AccountHistory::class),
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInvokeCallsWriterSave(): void
    {
        $accountHistory = $this->createMock(AccountHistory::class);

        $this->writer->expects($this->once())
            ->method('save')
            ->with($this->identicalTo($accountHistory));

        $this->assertInstanceOf(
            UpdateAccountHistory::class,
            ($this->updateAccountHistory)(
                accountHistory: $accountHistory,
            ),
        );
    }
}
