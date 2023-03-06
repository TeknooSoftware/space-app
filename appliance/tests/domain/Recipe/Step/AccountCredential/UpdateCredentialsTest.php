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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Recipe\Step\AccountCredential;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Service\DatesService;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Object\Persisted\AccountCredential;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Recipe\Step\AccountCredential\UpdateCredentials;
use Teknoo\Space\Writer\AccountCredentialWriter;

/**
 * Class UpdateCredentialsTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Recipe\Step\AccountCredential\UpdateCredentials
 */
class UpdateCredentialsTest extends TestCase
{
    private UpdateCredentials $updateCredentials;

    private AccountCredentialWriter|MockObject $writer;

    private DatesService|MockObject $datesService;

    private bool $prefereRealDate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->writer = $this->createMock(AccountCredentialWriter::class);
        $this->datesService = $this->createMock(DatesService::class);
        $this->prefereRealDate = true;
        $this->updateCredentials = new UpdateCredentials($this->writer, $this->datesService, $this->prefereRealDate);
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            UpdateCredentials::class,
            ($this->updateCredentials)(
                $this->createMock(ManagerInterface::class),
                'foo',
                'foo',
                'foo',
                $this->createMock(AccountCredential::class),
                $this->createMock(AccountHistory::class),
            ),
        );
    }
}
