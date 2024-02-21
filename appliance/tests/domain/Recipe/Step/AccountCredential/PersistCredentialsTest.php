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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\AccountCredential;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Recipe\Step\AccountCredential\PersistCredentials;
use Teknoo\Space\Writer\AccountCredentialWriter;

/**
 * Class PersistCredentialsTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Recipe\Step\AccountCredential\PersistCredentials
 */
class PersistCredentialsTest extends TestCase
{
    private PersistCredentials $persistCredentials;

    private AccountCredentialWriter|MockObject $writer;

    private DatesService|MockObject $datesService;

    private string $clusterName;

    private bool $prefereRealDate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->writer = $this->createMock(AccountCredentialWriter::class);
        $this->datesService = $this->createMock(DatesService::class);
        $this->clusterName = '42';
        $this->prefereRealDate = true;
        $this->persistCredentials = new PersistCredentials(
            $this->writer,
            $this->datesService,
            $this->clusterName,
            $this->prefereRealDate,
        );
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            PersistCredentials::class,
            ($this->persistCredentials)(
                $this->createMock(ManagerInterface::class),
                $this->createMock(ObjectInterface::class),
                'foo',
                'foo',
                'foo',
                'foo',
                'foo',
                'foo',
                'foo',
                'foo',
                'foo',
                'foo',
                $this->createMock(AccountHistory::class),
            ),
        );
    }
}
