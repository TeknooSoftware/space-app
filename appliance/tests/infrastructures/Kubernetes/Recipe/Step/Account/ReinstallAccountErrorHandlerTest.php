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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Kubernetes\Recipe\Step\Account;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Service\DatesService;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReinstallAccountErrorHandler;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Writer\AccountHistoryWriter;

/**
 * Class ReinstallAccountErrorHandlerTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account\ReinstallAccountErrorHandler
 */
class ReinstallAccountErrorHandlerTest extends TestCase
{
    private ReinstallAccountErrorHandler $reinstallAccountErrorHandler;

    private DatesService|MockObject $datesService;

    private AccountHistoryWriter|MockObject $writer;

    private bool $prefereRealDate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->datesService = $this->createMock(DatesService::class);
        $this->writer = $this->createMock(AccountHistoryWriter::class);
        $this->prefereRealDate = true;
        $this->reinstallAccountErrorHandler = new ReinstallAccountErrorHandler(
            $this->datesService,
            $this->writer,
            $this->prefereRealDate
        );
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            ReinstallAccountErrorHandler::class,
            ($this->reinstallAccountErrorHandler)(
                new \Exception('foo'),
                $this->createMock(ManagerInterface::class),
                $this->createMock(AccountHistory::class),
            )
        );
    }
}
