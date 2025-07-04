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
 * @link        http://https://teknoo.software/applications/space Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Writer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Writer\AccountClusterWriter;

/**
 * Class AccountClusterWriterTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountClusterWriter::class)]
class AccountClusterWriterTest extends TestCase
{
    private AccountClusterWriter $accountClusterWriter;

    private ManagerInterface|MockObject $manager;

    private DatesService|MockObject $datesService;

    protected bool $preferRealDateOnUpdate = false;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->createMock(ManagerInterface::class);
        $this->datesService = $this->createMock(DatesService::class);
        $this->preferRealDateOnUpdate = true;


        $this->accountClusterWriter = new AccountClusterWriter(
            $this->manager,
            $this->datesService,
            $this->preferRealDateOnUpdate,
        );
    }

    public function testSave(): void
    {
        self::assertInstanceOf(
            AccountClusterWriter::class,
            $this->accountClusterWriter->save(
                $this->createMock(ObjectInterface::class),
                $this->createMock(PromiseInterface::class),
                true,
            ),
        );
    }
}
