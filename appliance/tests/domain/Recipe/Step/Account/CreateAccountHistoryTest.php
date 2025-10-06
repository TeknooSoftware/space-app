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
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Recipe\Step\Account\CreateAccountHistory;
use Teknoo\Space\Writer\AccountHistoryWriter;

/**
 * Class CreateAccountHistoryTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(CreateAccountHistory::class)]
class CreateAccountHistoryTest extends TestCase
{
    private CreateAccountHistory $createAccountHistory;

    private AccountHistoryWriter&MockObject $writer;

    private DatesService&MockObject $datesService;

    private bool $preferRealDate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->writer = $this->createMock(AccountHistoryWriter::class);
        $this->datesService = $this->createMock(DatesService::class);
        $this->preferRealDate = true;
        $this->createAccountHistory = new CreateAccountHistory(
            $this->writer,
            $this->datesService,
            $this->preferRealDate
        );
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            CreateAccountHistory::class,
            ($this->createAccountHistory)(
                manager: $this->createMock(ManagerInterface::class),
                accountInstance: $this->createMock(Account::class),
                accountNamespace: 'foo',
                accountHistory: $this->createMock(AccountHistory::class),
            ),
        );
    }

    public function testInvokeWithExistingAccountHistory(): void
    {
        $accountHistory = $this->createMock(AccountHistory::class);

        $this->writer->expects($this->never())->method('save');
        $this->datesService->expects($this->never())->method('passMeTheDate');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->assertInstanceOf(
            CreateAccountHistory::class,
            ($this->createAccountHistory)(
                manager: $manager,
                accountInstance: $this->createMock(Account::class),
                accountNamespace: 'test-namespace',
                accountHistory: $accountHistory,
            ),
        );
    }

    public function testInvokeWithNullAccountHistory(): void
    {
        $account = $this->createMock(Account::class);

        $this->datesService->expects($this->once())
            ->method('passMeTheDate')
            ->willReturnCallback(function ($callback, $preferRealDate) {
                $this->assertTrue($preferRealDate);
                $callback(new \DateTime('2025-10-02 14:41:00'));
                return $this->datesService;
            });

        $this->writer->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($accountHistory) {
                return $accountHistory instanceof AccountHistory;
            }));

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with($this->callback(function ($workplan) {
                return isset($workplan[AccountHistory::class])
                    && $workplan[AccountHistory::class] instanceof AccountHistory;
            }));

        $this->assertInstanceOf(
            CreateAccountHistory::class,
            ($this->createAccountHistory)(
                manager: $manager,
                accountInstance: $account,
                accountNamespace: 'test-namespace',
                accountHistory: null,
            ),
        );
    }

    public function testInvokeWithPreferRealDateFalse(): void
    {
        $createAccountHistory = new CreateAccountHistory(
            $this->writer,
            $this->datesService,
            false
        );

        $account = $this->createMock(Account::class);

        $this->datesService->expects($this->once())
            ->method('passMeTheDate')
            ->willReturnCallback(function ($callback, $preferRealDate) {
                $this->assertFalse($preferRealDate);
                $callback(new \DateTime('2025-10-02 14:41:00'));
                return $this->datesService;
            });

        $this->writer->expects($this->once())
            ->method('save');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan');

        $this->assertInstanceOf(
            CreateAccountHistory::class,
            ($createAccountHistory)(
                manager: $manager,
                accountInstance: $account,
                accountNamespace: 'test-namespace',
            ),
        );
    }
}
