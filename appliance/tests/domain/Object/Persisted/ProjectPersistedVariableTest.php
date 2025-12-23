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

namespace Teknoo\Space\Tests\Unit\Object\Persisted;

use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Normalizer\EastNormalizerInterface;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Object\Persisted\PersistedVariableTrait;
use Teknoo\Space\Object\Persisted\ProjectPersistedVariable;

/**
 * Class ProjectPersistedVariableTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ProjectPersistedVariable::class)]
#[CoversTrait(PersistedVariableTrait::class)]
class ProjectPersistedVariableTest extends TestCase
{
    private ProjectPersistedVariable $persistedVariable;

    private Project&Stub $project;

    private string $id;

    private string $name;

    private string $value;

    private string $envName;

    private bool $secret;

    private string $encryptionAlgorithm;

    private bool $needEncryption;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->project = $this->createStub(Project::class);
        $this->id = '42';
        $this->name = '42';
        $this->value = '42';
        $this->envName = '42';
        $this->secret = true;
        $this->encryptionAlgorithm = 'rsa';
        $this->needEncryption = false;
        $this->persistedVariable = new ProjectPersistedVariable(
            project: $this->project,
            id: $this->id,
            name: $this->name,
            value: $this->value,
            envName: $this->envName,
            secret: $this->secret,
            encryptionAlgorithm: $this->encryptionAlgorithm,
            needEncryption: $this->needEncryption,
        );
    }

    public function testGetProject(): void
    {
        $this->assertSame(expected: $this->project, actual: $this->persistedVariable->getProject());
    }

    public function testGetName(): void
    {
        $this->assertEquals(expected: $this->name, actual: $this->persistedVariable->getName());
    }

    public function testGetValue(): void
    {
        $this->assertEquals(expected: $this->value, actual: $this->persistedVariable->getValue());
    }

    public function testGetEnvName(): void
    {
        $this->assertEquals(expected: $this->envName, actual: $this->persistedVariable->getEnvName());
    }

    public function testIsSecret(): void
    {
        $this->assertTrue($this->persistedVariable->isSecret());
    }

    public function testIsSecretWhenFalse(): void
    {
        $variable = new ProjectPersistedVariable(
            project: $this->project,
            id: $this->id,
            name: $this->name,
            value: $this->value,
            envName: $this->envName,
            secret: false,
            encryptionAlgorithm: $this->encryptionAlgorithm,
            needEncryption: $this->needEncryption,
        );

        $this->assertFalse($variable->isSecret());
    }

    public function testSetValue(): void
    {
        $result = $this->persistedVariable->setValue('newValue');

        $this->assertInstanceOf(expected: ProjectPersistedVariable::class, actual: $result);
        $this->assertEquals(expected: 'newValue', actual: $this->persistedVariable->getValue());
    }

    public function testSetEncryptedValue(): void
    {
        $variable = new ProjectPersistedVariable(
            project: $this->project,
            id: $this->id,
            name: $this->name,
            value: $this->value,
            envName: $this->envName,
            secret: false,
            encryptionAlgorithm: null,
            needEncryption: true,
        );

        $result = $variable->setEncryptedValue(algo: 'aes256', value: 'encryptedValue');

        $this->assertInstanceOf(expected: ProjectPersistedVariable::class, actual: $result);
        $this->assertEquals(expected: 'encryptedValue', actual: $variable->getValue());
        $this->assertEquals(expected: 'aes256', actual: $variable->getEncryptionAlgorithm());
        $this->assertFalse($variable->mustEncrypt());
    }

    public function testSetEncryptedValueThrowsExceptionWhenNotNeeded(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('This variable 42 not need encryption');

        $this->persistedVariable->setEncryptedValue(algo: 'aes256', value: 'encryptedValue');
    }

    public function testIsEncrypted(): void
    {
        $this->assertTrue($this->persistedVariable->isEncrypted());
    }

    public function testIsEncryptedWhenNoAlgorithm(): void
    {
        $variable = new ProjectPersistedVariable(
            project: $this->project,
            id: $this->id,
            name: $this->name,
            value: $this->value,
            envName: $this->envName,
            secret: false,
            encryptionAlgorithm: null,
            needEncryption: false,
        );

        $this->assertFalse($variable->isEncrypted());
    }

    public function testIsEncryptedWhenNeedsEncryption(): void
    {
        $variable = new ProjectPersistedVariable(
            project: $this->project,
            id: $this->id,
            name: $this->name,
            value: $this->value,
            envName: $this->envName,
            secret: false,
            encryptionAlgorithm: 'aes256',
            needEncryption: true,
        );

        $this->assertFalse($variable->isEncrypted());
    }

    public function testMustEncrypt(): void
    {
        $this->assertFalse($this->persistedVariable->mustEncrypt());
    }

    public function testMustEncryptWhenTrue(): void
    {
        $variable = new ProjectPersistedVariable(
            project: $this->project,
            id: $this->id,
            name: $this->name,
            value: $this->value,
            envName: $this->envName,
            secret: false,
            encryptionAlgorithm: null,
            needEncryption: true,
        );

        $this->assertTrue($variable->mustEncrypt());
    }

    public function testGetContent(): void
    {
        $this->assertEquals(expected: '42', actual: $this->persistedVariable->getContent());
    }

    public function testGetEncryptionAlgorithm(): void
    {
        $this->assertEquals(
            expected: $this->encryptionAlgorithm,
            actual: $this->persistedVariable->getEncryptionAlgorithm()
        );
    }

    public function testExportToMeDataWithSecret(): void
    {
        $normalizer = $this->createMock(EastNormalizerInterface::class);

        $normalizer->expects($this->once())
            ->method('injectData')
            ->with(
                $this->callback(function ($data) {
                    return true === $data['secret'] && null === $data['value'];
                })
            );

        $result = $this->persistedVariable->exportToMeData(
            normalizer: $normalizer,
            context: ['groups' => ['crud_variables']]
        );

        $this->assertInstanceOf(expected: ProjectPersistedVariable::class, actual: $result);
    }

    public function testExportToMeDataWithoutSecret(): void
    {
        $variable = new ProjectPersistedVariable(
            project: $this->project,
            id: $this->id,
            name: 'testName',
            value: 'testValue',
            envName: $this->envName,
            secret: false,
            encryptionAlgorithm: null,
            needEncryption: false,
        );

        $normalizer = $this->createMock(EastNormalizerInterface::class);

        $normalizer->expects($this->once())
            ->method('injectData')
            ->with(
                $this->callback(function ($data) {
                    return false === $data['secret'] && 'testValue' === $data['value'];
                })
            );

        $result = $variable->exportToMeData(
            normalizer: $normalizer,
            context: ['groups' => ['crud_variables']],
        );

        $this->assertInstanceOf(expected: ProjectPersistedVariable::class, actual: $result);
    }

    public function testCloneWithEncryptionAlgorithm(): void
    {
        $variable = new ProjectPersistedVariable(
            project: $this->project,
            id: $this->id,
            name: $this->name,
            value: $this->value,
            envName: $this->envName,
            secret: false,
            encryptionAlgorithm: null,
            needEncryption: true,
        );

        $result = $variable->cloneWith(content: 'newContent', encryptionAlgorithm: 'aes256');

        $this->assertSame(expected: $variable, actual: $result);
        $this->assertEquals(expected: 'newContent', actual: $variable->getValue());
        $this->assertEquals(expected: 'aes256', actual: $variable->getEncryptionAlgorithm());
    }

    public function testCloneWithoutEncryptionAlgorithm(): void
    {
        $result = $this->persistedVariable->cloneWith(content: 'newContent', encryptionAlgorithm: null);

        $this->assertNotSame(expected: $this->persistedVariable, actual: $result);
        $this->assertEquals(expected: 'newContent', actual: $result->getValue());
        $this->assertEquals(expected: $this->value, actual: $this->persistedVariable->getValue());
    }
}
