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

namespace Teknoo\Space\Tests\Unit\Object\Persisted;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Teknoo\East\Paas\Object\Project;
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
class ProjectPersistedVariableTest extends TestCase
{
    private ProjectPersistedVariable $persistedVariable;

    private Project|MockObject $project;

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

        $this->project = $this->createMock(Project::class);
        $this->id = '42';
        $this->name = '42';
        $this->value = '42';
        $this->envName = '42';
        $this->secret = true;
        $this->encryptionAlgorithm = 'rsa';
        $this->needEncryption = false;
        $this->persistedVariable = new ProjectPersistedVariable(
            $this->project,
            $this->id,
            $this->name,
            $this->value,
            $this->envName,
            $this->secret,
            $this->encryptionAlgorithm,
            $this->needEncryption,
        );
    }

    public function testGetProject(): void
    {
        $expected = $this->createMock(Project::class);
        $property = (new ReflectionClass(ProjectPersistedVariable::class))
            ->getProperty('project');
        $property->setAccessible(true);
        $property->setValue($this->persistedVariable, $expected);
        self::assertEquals($expected, $this->persistedVariable->getProject());
    }

    public function testGetName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(ProjectPersistedVariable::class))
            ->getProperty('name');
        $property->setAccessible(true);
        $property->setValue($this->persistedVariable, $expected);
        self::assertEquals($expected, $this->persistedVariable->getName());
    }

    public function testGetValue(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(ProjectPersistedVariable::class))
            ->getProperty('value');
        $property->setAccessible(true);
        $property->setValue($this->persistedVariable, $expected);
        self::assertEquals($expected, $this->persistedVariable->getValue());
    }

    public function testGetEnvName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(ProjectPersistedVariable::class))
            ->getProperty('envName');
        $property->setAccessible(true);
        $property->setValue($this->persistedVariable, $expected);
        self::assertEquals($expected, $this->persistedVariable->getEnvName());
    }

    public function testIsSecret(): void
    {
        self::assertIsBool(
            $this->persistedVariable->isSecret(),
        );
    }
}
