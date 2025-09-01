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
use ReflectionClass;
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
        $this->jobVar = new JobVar(
            id: $this->id,
            name: $this->name,
            value: $this->value,
            persisted: $this->persisted,
            secret: $this->secret,
            wasSecret: $this->wasSecret,
            persistedVar: null
        );

        $expected = '42';
        $property = new ReflectionClass(JobVar::class)
            ->getProperty('id');
        $property->setValue($this->jobVar, $expected);
        $this->assertEquals($expected, $this->jobVar->getId());

        $this->jobVar = new JobVar(
            id: $this->id,
            name: $this->name,
            value: $this->value,
            persisted: $this->persisted,
            secret: $this->secret,
            wasSecret: $this->wasSecret,
            persistedVar: $this->persistedVar
        );

        $this->persistedVar
            ->method('getId')
            ->willReturn('24');
        $this->assertEquals('24', $this->jobVar->getId());
    }

    public function testExport(): void
    {
        $this->assertInstanceOf(
            JobVar::class,
            $cloned = $this->jobVar->export(),
        );

        $this->assertNotSame(
            $cloned,
            $this->jobVar,
        );

        $property = new ReflectionClass(JobVar::class)
            ->getProperty('persistedVar');
        $this->assertNull(
            $property->getValue($cloned)
        );
    }
}
