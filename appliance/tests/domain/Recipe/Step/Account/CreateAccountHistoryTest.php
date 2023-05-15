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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Account;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Service\DatesService;
use Teknoo\East\Foundation\Manager\ManagerInterface;
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
 * @covers \Teknoo\Space\Recipe\Step\Account\CreateAccountHistory
 */
class CreateAccountHistoryTest extends TestCase
{
    private CreateAccountHistory $createAccountHistory;

    private AccountHistoryWriter|MockObject $writer;

    private DatesService|MockObject $datesService;

    private bool $prefereRealDate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->writer = $this->createMock(AccountHistoryWriter::class);
        $this->datesService = $this->createMock(DatesService::class);
        $this->prefereRealDate = true;
        $this->createAccountHistory = new CreateAccountHistory(
            $this->writer,
            $this->datesService,
            $this->prefereRealDate
        );
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            CreateAccountHistory::class,
            ($this->createAccountHistory)(
                $this->createMock(ManagerInterface::class),
                $this->createMock(Account::class),
                'foo',
                $this->createMock(AccountHistory::class),
            ),
        );
    }
}
