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

namespace Teknoo\Space\Tests\Unit\Object\DTO;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountData;

/**
 * Class SpaceAccountTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Object\DTO\SpaceAccount
 */
class SpaceAccountTest extends TestCase
{
    private SpaceAccount $spaceAccount;

    private Account|MockObject $account;

    private AccountData|MockObject $accountData;

    private iterable $variables;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->account = $this->createMock(Account::class);
        $this->accountData = $this->createMock(AccountData::class);
        $this->variables = [];
        $this->spaceAccount = new SpaceAccount(
            account: $this->account,
            accountData: $this->accountData,
            variables: $this->variables
        );
    }

    public function testGetId(): void
    {
        $this->account
            ->expects($this->any())
            ->method('getId')
            ->willReturn('foo');

        self::assertEquals(
            'foo',
            $this->spaceAccount->getId()
        );
    }

    public function testToString(): void
    {
        $this->account
            ->expects($this->any())
            ->method('__toString')
            ->willReturn('foo');

        self::assertEquals(
            'foo',
            (string) $this->spaceAccount
        );
    }
}
