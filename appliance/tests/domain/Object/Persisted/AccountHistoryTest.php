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

namespace Teknoo\Space\Tests\Unit\Object\Persisted;

use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\History;
use Teknoo\Space\Object\Persisted\AccountHistory;

/**
 * Class AccountHistoryTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Object\Persisted\AccountHistory
 */
class AccountHistoryTest extends TestCase
{
    private AccountHistory $accountHistory;

    private Account|MockObject $account;

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
        self::assertInstanceOf(
            AccountHistory::class,
            $this->accountHistory->addToHistory('foo', new DateTime('2023-03-17')),
        );
    }

    public function testSetHistory(): void
    {
        $expected = $this->createMock(History::class);
        $property = (new ReflectionClass(AccountHistory::class))
            ->getProperty('history');
        $property->setAccessible(true);
        $this->accountHistory->setHistory($expected);
        self::assertEquals($expected, $property->getValue($this->accountHistory));
    }

    public function testPassMeYouHistory(): void
    {
        $final = null;
        self::assertInstanceOf(
            AccountHistory::class,
            $this->accountHistory->setHistory($this->createMock(History::class))->passMeYouHistory(
                function ($value) use (&$final) {
                    $final = $value;
                }
            ),
        );

        self::assertInstanceOf(
            expected: History::class,
            actual: $final,
        );
    }
}
