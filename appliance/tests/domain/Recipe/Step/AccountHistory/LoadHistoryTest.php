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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\AccountHistory;

use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\History;
use Teknoo\Space\Loader\AccountHistoryLoader;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Recipe\Step\AccountHistory\LoadHistory;
use Teknoo\Space\Writer\AccountHistoryWriter;

/**
 * Class LoadHistoryTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(LoadHistory::class)]
class LoadHistoryTest extends TestCase
{
    private LoadHistory $loadHistory;

    private AccountHistoryLoader&MockObject $loader;

    private AccountHistoryWriter&MockObject $writer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = $this->createMock(AccountHistoryLoader::class);
        $this->writer = $this->createMock(AccountHistoryWriter::class);
        $this->loadHistory = new LoadHistory($this->loader, $this->writer);
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            LoadHistory::class,
            ($this->loadHistory)(
                manager: $this->createMock(ManagerInterface::class),
                accountInstance: $this->createMock(Account::class),
                bag: $this->createMock(ParametersBag::class),
            ),
        );
    }

    public function testInvokeWithSuccessfulLoad(): void
    {
        $account = $this->createMock(Account::class);
        $history = $this->createMock(History::class);
        $accountHistory = $this->createMock(AccountHistory::class);
        $accountHistory->expects($this->once())
            ->method('passMeYouHistory')
            ->willReturnCallback(function ($callback) use ($history, $accountHistory) {
                $callback($history);
                return $accountHistory;
            });

        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->exactly(2))
            ->method('set')
            ->willReturnCallback(function ($key, $value) use ($accountHistory, $history, $bag) {
                $this->assertContains($key, ['accountHistory', 'accountHistoryRoot']);
                if ($key === 'accountHistory') {
                    $this->assertSame($accountHistory, $value);
                } elseif ($key === 'accountHistoryRoot') {
                    $this->assertSame($history, $value);
                }
                return $bag;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) use ($accountHistory) {
                    return isset($workplan[AccountHistory::class])
                        && $workplan[AccountHistory::class] === $accountHistory;
                })
            );

        $this->loader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($query, $promise) use ($accountHistory) {
                $promise->success($accountHistory);
                return $this->loader;
            });

        $this->writer->expects($this->never())
            ->method('save');

        $result = ($this->loadHistory)(
            manager: $manager,
            accountInstance: $account,
            bag: $bag,
        );

        $this->assertInstanceOf(LoadHistory::class, $result);
    }

    public function testInvokeWithNonDomainExceptionPassesErrorToManager(): void
    {
        $account = $this->createMock(Account::class);
        $exception = new RuntimeException('Some error');

        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->never())
            ->method('set');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with($exception);
        $manager->expects($this->never())
            ->method('updateWorkPlan');

        $this->loader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($query, $promise) use ($exception) {
                $promise->fail($exception);
                return $this->loader;
            });

        $this->writer->expects($this->never())
            ->method('save');

        $result = ($this->loadHistory)(
            manager: $manager,
            accountInstance: $account,
            bag: $bag,
        );

        $this->assertInstanceOf(LoadHistory::class, $result);
    }

    public function testInvokeWithDomainExceptionCreatesNewHistory(): void
    {
        $account = $this->createMock(Account::class);
        $exception = new DomainException('Account history not found');

        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->once())
            ->method('set')
            ->with('accountHistory', $this->isInstanceOf(AccountHistory::class))
            ->willReturn($bag);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('error');
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan[AccountHistory::class])
                        && $workplan[AccountHistory::class] instanceof AccountHistory;
                })
            );

        $this->loader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($query, $promise) use ($exception) {
                $promise->fail($exception);
                return $this->loader;
            });

        $this->writer->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(AccountHistory::class))
            ->willReturn($this->writer);

        $result = ($this->loadHistory)(
            manager: $manager,
            accountInstance: $account,
            bag: $bag,
        );

        $this->assertInstanceOf(LoadHistory::class, $result);
    }
}
