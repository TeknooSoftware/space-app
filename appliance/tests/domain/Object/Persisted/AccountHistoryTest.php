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

namespace Teknoo\Space\Tests\Unit\Object\Persisted;

use DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\History;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Object\Persisted\AccountHistory;

/**
 * Class AccountHistoryTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountHistory::class)]
class AccountHistoryTest extends TestCase
{
    private AccountHistory $accountHistory;

    private Account&MockObject $account;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->account = $this->createMock(Account::class);
        $this->accountHistory = new AccountHistory($this->account);
    }

    public function testAddToHistory(): void
    {
        $this->assertInstanceOf(
            AccountHistory::class,
            $this->accountHistory->addToHistory('foo', new DateTime('2023-03-17')),
        );
    }

    public function testAddToHistoryWithIsFinal(): void
    {
        $this->assertInstanceOf(
            AccountHistory::class,
            $this->accountHistory->addToHistory('foo', new DateTime('2023-03-17'), true),
        );
    }

    public function testAddToHistoryWithExtra(): void
    {
        $extra = ['key' => 'value', 'another' => 'data'];
        $this->assertInstanceOf(
            AccountHistory::class,
            $this->accountHistory->addToHistory('foo', new DateTime('2023-03-17'), false, $extra),
        );
    }

    public function testAddToHistoryWithAllParameters(): void
    {
        $extra = ['key' => 'value'];
        $this->assertInstanceOf(
            AccountHistory::class,
            $this->accountHistory->addToHistory('foo', new DateTime('2023-03-17'), true, $extra),
        );
    }

    public function testSetHistory(): void
    {
        $history = $this->createMock(History::class);
        $history->expects($this->once())
            ->method('limit')
            ->with(150)
            ->willReturnSelf();

        $result = $this->accountHistory->setHistory($history);

        $this->assertInstanceOf(AccountHistory::class, $result);
    }

    public function testSetHistoryWithNull(): void
    {
        $result = $this->accountHistory->setHistory(null);

        $this->assertInstanceOf(AccountHistory::class, $result);
    }

    public function testPassMeYouHistory(): void
    {
        $final = null;
        $this->assertInstanceOf(
            AccountHistory::class,
            $this->accountHistory->setHistory($this->createMock(History::class))->passMeYouHistory(
                function ($value) use (&$final): void {
                    $final = $value;
                }
            ),
        );

        $this->assertInstanceOf(
            expected: History::class,
            actual: $final,
        );
    }

    public function testPassMeYouHistoryWithNullHistory(): void
    {
        $called = false;
        $result = $this->accountHistory->passMeYouHistory(
            function () use (&$called): void {
                $called = true;
            }
        );

        $this->assertInstanceOf(AccountHistory::class, $result);
        $this->assertFalse($called, 'Callback should not be called when history is null');
    }

    public function testVerifyAccessToUser(): void
    {
        $user = $this->createMock(User::class);
        $promise = $this->createMock(PromiseInterface::class);

        $this->account->expects($this->once())
            ->method('__call')
            ->with('verifyAccessToUser');

        $result = $this->accountHistory->verifyAccessToUser($user, $promise);

        $this->assertInstanceOf(AccountHistory::class, $result);
    }
}
