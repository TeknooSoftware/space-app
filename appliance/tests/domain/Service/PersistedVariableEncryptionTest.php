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

namespace Teknoo\Space\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Paas\Contracts\Security\EncryptionInterface;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Contracts\Object\EncryptableVariableInterface;
use Teknoo\Space\Service\PersistedVariableEncryption;

/**
 * Class PersistedVariableEncryptionTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(PersistedVariableEncryption::class)]
class PersistedVariableEncryptionTest extends TestCase
{
    private PersistedVariableEncryption $persistedVariableEncryption;

    private EncryptionInterface&MockObject $encryptionInterface;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->encryptionInterface = $this->createMock(EncryptionInterface::class);

        $this->persistedVariableEncryption = new PersistedVariableEncryption(
            $this->encryptionInterface,
            false,
        );
    }

    public function testSetAgentMode(): void
    {
        $this->assertInstanceOf(
            PersistedVariableEncryption::class,
            $this->persistedVariableEncryption->setAgentMode(true),
        );
    }

    public function testEncrypt(): void
    {
        $this->assertInstanceOf(
            PersistedVariableEncryption::class,
            $this->persistedVariableEncryption->encrypt(
                $this->createMock(EncryptableVariableInterface::class),
                $this->createMock(PromiseInterface::class),
            ),
        );
    }

    public function testDecrypt(): void
    {
        $this->assertInstanceOf(
            PersistedVariableEncryption::class,
            $this->persistedVariableEncryption->decrypt(
                $this->createMock(EncryptableVariableInterface::class),
                $this->createMock(PromiseInterface::class),
            ),
        );
    }

    public function testConstructWithNullService(): void
    {
        $service = new PersistedVariableEncryption(null, false);
        $this->assertInstanceOf(PersistedVariableEncryption::class, $service);
    }

    public function testEncryptWithNoService(): void
    {
        $service = new PersistedVariableEncryption(null, false);
        $variable = $this->createMock(EncryptableVariableInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects($this->once())
            ->method('success')
            ->with($variable);

        $result = $service->encrypt($variable, $promise);
        $this->assertInstanceOf(PersistedVariableEncryption::class, $result);
    }

    public function testEncryptWhenAlreadyEncrypted(): void
    {
        $variable = $this->createMock(EncryptableVariableInterface::class);
        $variable->expects($this->once())
            ->method('isEncrypted')
            ->willReturn(true);

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->with($variable);

        $result = $this->persistedVariableEncryption->encrypt($variable, $promise);
        $this->assertInstanceOf(PersistedVariableEncryption::class, $result);
    }

    public function testEncryptInAgentMode(): void
    {
        $this->persistedVariableEncryption->setAgentMode(true);

        $variable = $this->createMock(EncryptableVariableInterface::class);
        $variable->expects($this->once())
            ->method('isEncrypted')
            ->willReturn(false);

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->isInstanceOf(\RuntimeException::class));

        $result = $this->persistedVariableEncryption->encrypt($variable, $promise);
        $this->assertInstanceOf(PersistedVariableEncryption::class, $result);
    }

    public function testEncryptCallsService(): void
    {
        $variable = $this->createMock(EncryptableVariableInterface::class);
        $variable->expects($this->once())
            ->method('isEncrypted')
            ->willReturn(false);

        $promise = $this->createMock(PromiseInterface::class);

        $this->encryptionInterface->expects($this->once())
            ->method('encrypt')
            ->with($variable, $promise, true);

        $result = $this->persistedVariableEncryption->encrypt($variable, $promise);
        $this->assertInstanceOf(PersistedVariableEncryption::class, $result);
    }

    public function testDecryptWithNoService(): void
    {
        $service = new PersistedVariableEncryption(null, true);
        $variable = $this->createMock(EncryptableVariableInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects($this->once())
            ->method('success')
            ->with($variable);

        $result = $service->decrypt($variable, $promise);
        $this->assertInstanceOf(PersistedVariableEncryption::class, $result);
    }

    public function testDecryptWhenMustEncrypt(): void
    {
        $this->persistedVariableEncryption->setAgentMode(true);

        $variable = $this->createMock(EncryptableVariableInterface::class);
        $variable->expects($this->once())
            ->method('mustEncrypt')
            ->willReturn(true);

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->with($variable);

        $result = $this->persistedVariableEncryption->decrypt($variable, $promise);
        $this->assertInstanceOf(PersistedVariableEncryption::class, $result);
    }

    public function testDecryptWhenNotInAgentMode(): void
    {
        $variable = $this->createMock(EncryptableVariableInterface::class);
        $variable->expects($this->once())
            ->method('mustEncrypt')
            ->willReturn(false);

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->never())
            ->method('success');
        $promise->expects($this->once())
            ->method('fail');

        $this->encryptionInterface->expects($this->never())
            ->method('decrypt');

        $result = $this->persistedVariableEncryption->decrypt($variable, $promise);
        $this->assertInstanceOf(PersistedVariableEncryption::class, $result);
    }

    public function testDecryptCallsService(): void
    {
        $this->persistedVariableEncryption->setAgentMode(true);

        $variable = $this->createMock(EncryptableVariableInterface::class);
        $variable->expects($this->once())
            ->method('mustEncrypt')
            ->willReturn(false);

        $promise = $this->createMock(PromiseInterface::class);

        $this->encryptionInterface->expects($this->once())
            ->method('decrypt')
            ->with($variable, $promise, true);

        $result = $this->persistedVariableEncryption->decrypt($variable, $promise);
        $this->assertInstanceOf(PersistedVariableEncryption::class, $result);
    }
}
