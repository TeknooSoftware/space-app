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
 * @link        https://teknoo.software/applications/space Project website
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
use Teknoo\Space\Service\PersistedVariableEncryption;
use Teknoo\Space\Writer\AccountPersistedVariableWriter;

/**
 * Class AccountPersistedVariableWriterTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountPersistedVariableWriter::class)]
class AccountPersistedVariableWriterTest extends TestCase
{
    private AccountPersistedVariableWriter $accountPersistedVariableWriter;

    private ManagerInterface|MockObject $manager;

    private PersistedVariableEncryption|MockObject $persistedVariableEncryption;

    private DatesService|MockObject $datesService;

    protected bool $preferRealDateOnUpdate = false;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->createMock(ManagerInterface::class);
        $this->persistedVariableEncryption = $this->createMock(PersistedVariableEncryption::class);
        $this->datesService = $this->createMock(DatesService::class);
        $this->preferRealDateOnUpdate = true;

        $this->accountPersistedVariableWriter = new AccountPersistedVariableWriter(
            $this->manager,
            $this->persistedVariableEncryption,
            $this->datesService,
            $this->preferRealDateOnUpdate,
        );
    }

    public function testSave(): void
    {
        self::assertInstanceOf(
            AccountPersistedVariableWriter::class,
            $this->accountPersistedVariableWriter->save(
                $this->createMock(ObjectInterface::class),
                $this->createMock(PromiseInterface::class),
                true,
            ),
        );
    }
}
