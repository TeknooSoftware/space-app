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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\AccountEnvironment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Recipe\Step\AccountEnvironment\RemoveEnvironment;
use Teknoo\Space\Writer\AccountEnvironmentWriter;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * Class RemoveCredentialTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(RemoveEnvironment::class)]
class RemoveEnvironmentTest extends TestCase
{
    private RemoveEnvironment $removeCredentials;

    private AccountEnvironmentWriter&MockObject $writer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->writer = $this->createMock(AccountEnvironmentWriter::class);
        $this->removeCredentials = new RemoveEnvironment($this->writer);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            RemoveEnvironment::class,
            ($this->removeCredentials)(
                accountEnvironment: $this->createStub(AccountEnvironment::class),
            ),
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInvokeWithEnvironmentRemoval(): void
    {
        $environment = $this->createMock(AccountEnvironment::class);

        $this->writer->expects($this->once())
            ->method('remove')
            ->with($environment);

        $this->assertInstanceOf(
            RemoveEnvironment::class,
            ($this->removeCredentials)(
                accountEnvironment: $environment,
            ),
        );
    }

    public function testInvokeWithNullEnvironment(): void
    {
        $this->writer->expects($this->never())
            ->method('remove');

        $this->assertInstanceOf(
            RemoveEnvironment::class,
            ($this->removeCredentials)(
                accountEnvironment: null,
            ),
        );
    }
}
