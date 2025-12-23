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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\AccountRegistry;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\Space\Object\Persisted\AccountRegistry;
use Teknoo\Space\Recipe\Step\AccountRegistry\RemoveRegistryCredential;
use Teknoo\Space\Writer\AccountRegistryWriter;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * Class RemoveRegistrysTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(RemoveRegistryCredential::class)]
class RemoveRegistryCredentialTest extends TestCase
{
    private RemoveRegistryCredential $removeRegistryCredential;

    private AccountRegistryWriter&MockObject $writer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->writer = $this->createMock(AccountRegistryWriter::class);
        $this->removeRegistryCredential = new RemoveRegistryCredential($this->writer);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            RemoveRegistryCredential::class,
            ($this->removeRegistryCredential)(
                registry: $this->createStub(AccountRegistry::class),
            ),
        );
    }

    public function testInvokeWithNullRegistry(): void
    {
        $this->writer->expects($this->never())->method('remove');

        $result = ($this->removeRegistryCredential)(
            registry: null,
        );

        $this->assertInstanceOf(RemoveRegistryCredential::class, $result);
    }

    public function testInvokeWithAccountRegistry(): void
    {
        $registry = $this->createStub(AccountRegistry::class);

        $this->writer->expects($this->once())
            ->method('remove')
            ->with($registry)
            ->willReturnSelf();

        $result = ($this->removeRegistryCredential)(
            registry: $registry,
        );

        $this->assertInstanceOf(RemoveRegistryCredential::class, $result);
    }
}
