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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Kubernetes\Recipe\Step\Account;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
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
 */
#[CoversClass(ReinstallAccountErrorHandler::class)]
class ReinstallAccountErrorHandlerTest extends TestCase
{
    private ReinstallAccountErrorHandler $reinstallAccountErrorHandler;

    private DatesService&MockObject $datesService;

    private AccountHistoryWriter&MockObject $writer;

    private bool $preferRealDate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->datesService = $this->createMock(DatesService::class);
        $this->writer = $this->createMock(AccountHistoryWriter::class);
        $this->preferRealDate = true;
        $this->reinstallAccountErrorHandler = new ReinstallAccountErrorHandler(
            $this->datesService,
            $this->writer,
            $this->preferRealDate
        );
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            ReinstallAccountErrorHandler::class,
            ($this->reinstallAccountErrorHandler)(
                new \Exception('foo'),
                $this->createMock(ManagerInterface::class),
                $this->createMock(AccountHistory::class),
            )
        );
    }
}
