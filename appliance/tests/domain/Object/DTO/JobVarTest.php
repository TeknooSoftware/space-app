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

namespace Teknoo\Space\Tests\Unit\Object\DTO;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\Space\Object\DTO\JobVar;
use Teknoo\Space\Object\Persisted\ProjectPersistedVariable;

/**
 * Class JobVarTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(JobVar::class)]
class JobVarTest extends TestCase
{
    private JobVar $jobVar;

    private string $id;

    private string $name;

    private string $value;

    private bool $persisted;

    private bool $secret;

    private bool $wasSecret;

    private ProjectPersistedVariable&MockObject $persistedVar;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->id = '42';
        $this->name = '42';
        $this->value = '42';
        $this->persisted = true;
        $this->secret = true;
        $this->wasSecret = true;
        $this->persistedVar = $this->createMock(ProjectPersistedVariable::class);
        $this->jobVar = new JobVar(
            id: $this->id,
            name: $this->name,
            value: $this->value,
            persisted: $this->persisted,
            secret: $this->secret,
            wasSecret: $this->wasSecret,
            persistedVar: $this->persistedVar
        );
    }

    public function testGetId(): void
    {
        // Test with no persistedVar - should return the id passed to constructor
        $jobVar = new JobVar(
            id: '42',
            name: $this->name,
            value: $this->value,
            persisted: $this->persisted,
            secret: $this->secret,
            wasSecret: $this->wasSecret,
            persistedVar: null
        );

        $this->assertEquals('42', $jobVar->getId());

        // Test with persistedVar - should return id from persistedVar
        $persistedVar = $this->createMock(ProjectPersistedVariable::class);
        $persistedVar
            ->method('getId')
            ->willReturn('24');

        $jobVar = new JobVar(
            id: $this->id,
            name: $this->name,
            value: $this->value,
            persisted: $this->persisted,
            secret: $this->secret,
            wasSecret: $this->wasSecret,
            persistedVar: $persistedVar
        );

        $this->assertEquals('24', $jobVar->getId());
    }

    public function testExport(): void
    {
        // Create a JobVar with persistedVar that has an ID
        $persistedVar = $this->createMock(ProjectPersistedVariable::class);
        $persistedVar->method('getId')->willReturn('persisted-id');

        $jobVar = new JobVar(
            id: 'direct-id',
            name: 'test',
            secret: true,
            wasSecret: true,
            persistedVar: $persistedVar
        );

        // Before export, getId should return persistedVar's ID
        $this->assertEquals('persisted-id', $jobVar->getId());
        $this->assertTrue($jobVar->wasSecret);

        // Export the JobVar
        $cloned = $jobVar->export();

        // Verify it's a new instance
        $this->assertInstanceOf(JobVar::class, $cloned);
        $this->assertNotSame($cloned, $jobVar);

        // After export, persistedVar should be null, so getId should return direct id
        $this->assertEquals('direct-id', $cloned->getId());

        // wasSecret should be false after export
        $this->assertFalse($cloned->wasSecret);
    }

    public function testIsSecret(): void
    {
        $jobVar = new JobVar(secret: true);
        $this->assertTrue($jobVar->isSecret());

        $jobVar = new JobVar(secret: false);
        $this->assertFalse($jobVar->isSecret());
    }

    public function testIsEncrypted(): void
    {
        $jobVar = new JobVar(encryptionAlgorithm: 'aes-256');
        $this->assertTrue($jobVar->isEncrypted());

        $jobVar = new JobVar(encryptionAlgorithm: null);
        $this->assertFalse($jobVar->isEncrypted());

        $jobVar = new JobVar(encryptionAlgorithm: '');
        $this->assertFalse($jobVar->isEncrypted());
    }

    public function testMustEncrypt(): void
    {
        $this->assertFalse($this->jobVar->mustEncrypt());
    }

    public function testGetContent(): void
    {
        $jobVar = new JobVar(value: 'test-content');
        $this->assertEquals('test-content', $jobVar->getContent());

        $jobVar = new JobVar(value: null);
        $this->assertEquals('', $jobVar->getContent());
    }

    public function testGetEncryptionAlgorithm(): void
    {
        $jobVar = new JobVar(encryptionAlgorithm: 'aes-256');
        $this->assertEquals('aes-256', $jobVar->getEncryptionAlgorithm());

        $jobVar = new JobVar(encryptionAlgorithm: null);
        $this->assertNull($jobVar->getEncryptionAlgorithm());
    }

    public function testCloneWith(): void
    {
        $cloned = $this->jobVar->cloneWith('new-content', 'new-algo');

        $this->assertInstanceOf(JobVar::class, $cloned);
        $this->assertNotSame($cloned, $this->jobVar);
        $this->assertEquals('new-content', $cloned->getContent());
        $this->assertEquals('new-algo', $cloned->getEncryptionAlgorithm());
    }

    public function testJsonSerialize(): void
    {
        $jobVar = new JobVar(
            id: 'test-id',
            name: 'test-name',
            value: 'test-value',
            persisted: true,
            secret: true,
            encryptionAlgorithm: 'aes-256',
            canPersist: true,
        );

        $result = $jobVar->jsonSerialize();

        $this->assertIsArray($result);
        $this->assertEquals('test-id', $result['id']);
        $this->assertEquals('test-name', $result['name']);
        $this->assertEquals('test-value', $result['value']);
        $this->assertTrue($result['persisted']);
        $this->assertTrue($result['secret']);
        $this->assertEquals('aes-256', $result['encryptionAlgorithm']);
        $this->assertTrue($result['canPersist']);
    }

    public function testConstructorDefaultWasSecret(): void
    {
        $jobVar = new JobVar(secret: true);
        $this->assertTrue($jobVar->wasSecret);

        $jobVar = new JobVar(secret: false);
        $this->assertFalse($jobVar->wasSecret);
    }
}
