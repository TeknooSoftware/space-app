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
use Teknoo\Space\Object\DTO\NewJob;

/**
 * Class NewJobTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(NewJob::class)]
class NewJobTest extends TestCase
{
    private NewJob $newJob;

    private string $newJobId;

    /**
     * @var MockObject[]
     */
    private array $variables;

    private string $projectId;

    private string $accountId;

    private string $envName;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->newJobId = '42';
        $this->variables = [
            $this->createMock(JobVar::class),
            $this->createMock(JobVar::class),
        ];
        $this->projectId = '42';
        $this->accountId = '42';
        $this->envName = '42';
        $this->newJob = new NewJob(
            newJobId: $this->newJobId,
            variables: $this->variables,
            projectId: $this->projectId,
            accountId: $this->accountId,
            envName: $this->envName
        );
    }

    public function testConstructWithProvidedId(): void
    {
        $newJob = new NewJob(newJobId: 'custom-id');
        $this->assertEquals('custom-id', $newJob->newJobId);
    }

    public function testConstructWithEmptyId(): void
    {
        $newJob = new NewJob();
        $this->assertNotEmpty($newJob->newJobId);
        $this->assertEquals(48, strlen($newJob->newJobId)); // bin2hex(random_bytes(24)) = 48 chars
    }

    public function testConstructWithAllParameters(): void
    {
        $variables = [$this->createMock(JobVar::class)];
        $storageProvisioner = ['cluster1' => 'provisioner1'];

        $newJob = new NewJob(
            newJobId: 'test-id',
            variables: $variables,
            projectId: 'proj-123',
            accountId: 'acc-456',
            envName: 'production',
            storageProvisionerPerCluster: $storageProvisioner
        );

        $this->assertEquals('test-id', $newJob->newJobId);
        $this->assertSame($variables, $newJob->variables);
        $this->assertEquals('proj-123', $newJob->projectId);
        $this->assertEquals('acc-456', $newJob->accountId);
        $this->assertEquals('production', $newJob->envName);
        $this->assertEquals($storageProvisioner, $newJob->storageProvisionerPerCluster);
    }

    public function testExport(): void
    {
        $this->variables[0]->expects($this->once())
            ->method('export')
            ->willReturnSelf();
        $this->variables[1]->expects($this->once())
            ->method('export')
            ->willReturnSelf();

        $this->assertInstanceOf(
            NewJob::class,
            $export = $this->newJob->export(),
        );

        $this->assertNotSame(
            $export,
            $this->newJob,
        );
    }

    public function testGetMessageWithoutEncryption(): void
    {
        $var1 = new JobVar(name: 'var1', value: 'value1');
        $var2 = new JobVar(name: 'var2', value: 'value2');

        $newJob = new NewJob(
            newJobId: 'test-id',
            variables: [$var1, $var2]
        );

        $message = $newJob->getMessage();
        $this->assertJson($message);

        $decoded = json_decode($message, true);
        $this->assertIsArray($decoded);
        $this->assertCount(2, $decoded);
    }

    public function testGetMessageWithEncryption(): void
    {
        $var1 = new JobVar(name: 'var1', value: 'value1');

        $newJob = new NewJob(
            newJobId: 'test-id',
            variables: [$var1]
        );

        // Clone with encryption to set encryptedVariables
        $encrypted = $newJob->cloneWith('encrypted-content', 'aes-256');

        $message = $encrypted->getMessage();
        $this->assertEquals('encrypted-content', $message);
    }

    public function testGetContent(): void
    {
        $var1 = new JobVar(name: 'var1', value: 'value1');

        $newJob = new NewJob(
            newJobId: 'test-id',
            variables: [$var1]
        );

        // getContent should call getMessage
        $content = $newJob->getContent();
        $this->assertEquals($newJob->getMessage(), $content);
    }

    public function testGetEncryptionAlgorithm(): void
    {
        $newJob = new NewJob(newJobId: 'test-id');
        $this->assertNull($newJob->getEncryptionAlgorithm());

        // After cloning with encryption
        $encrypted = $newJob->cloneWith('content', 'aes-256');
        $this->assertEquals('aes-256', $encrypted->getEncryptionAlgorithm());
    }

    public function testCloneWithEncryption(): void
    {
        $var1 = new JobVar(name: 'var1', value: 'value1');

        $newJob = new NewJob(
            newJobId: 'test-id',
            variables: [$var1]
        );

        $encrypted = $newJob->cloneWith('encrypted-data', 'aes-256-gcm');

        $this->assertInstanceOf(NewJob::class, $encrypted);
        $this->assertNotSame($encrypted, $newJob);
        $this->assertEquals('aes-256-gcm', $encrypted->getEncryptionAlgorithm());
        $this->assertEquals('encrypted-data', $encrypted->getMessage());
        $this->assertEmpty($encrypted->variables);
    }

    public function testCloneWithoutEncryption(): void
    {
        $var1 = new JobVar(id: 'id1', name: 'var1', value: 'value1', persisted: false, secret: false);
        $var2 = new JobVar(id: 'id2', name: 'var2', value: 'value2', persisted: true, secret: true);

        $newJob = new NewJob(
            newJobId: 'test-id',
            variables: [$var1, $var2]
        );

        // First encrypt
        $encrypted = $newJob->cloneWith($newJob->getMessage(), 'aes-256');

        // Then decrypt (clone with null encryption)
        $decrypted = $encrypted->cloneWith($newJob->getMessage(), null);

        $this->assertInstanceOf(NewJob::class, $decrypted);
        $this->assertNotSame($decrypted, $encrypted);
        $this->assertNull($decrypted->getEncryptionAlgorithm());
        $this->assertCount(2, $decrypted->variables);
        $this->assertInstanceOf(JobVar::class, $decrypted->variables[0]);
        $this->assertEquals('var1', $decrypted->variables[0]->name);
        $this->assertEquals('var2', $decrypted->variables[1]->name);
    }
}
