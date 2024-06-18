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

namespace Teknoo\Space\Tests\Unit\Object\DTO;

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
 * @covers \Teknoo\Space\Object\DTO\JobVar
 */
class JobVarTest extends TestCase
{
    private JobVar $jobVar;

    private string $id;

    private string $name;

    private string $value;

    private bool $persisted;

    private bool $secret;

    private bool $wasSecret;

    private ProjectPersistedVariable|MockObject $persistedVar;

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
        $property = (new ReflectionClass(JobVar::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->jobVar, $expected);
        self::assertEquals($expected, $this->jobVar->getId());

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
            ->expects($this->any())
            ->method('getId')
            ->willReturn('24');
        self::assertEquals('24', $this->jobVar->getId());
    }

    public function testExport(): void
    {
        self::assertInstanceOf(
            JobVar::class,
            $cloned = $this->jobVar->export(),
        );

        self::assertNotSame(
            $cloned,
            $this->jobVar,
        );

        $property = (new ReflectionClass(JobVar::class))
            ->getProperty('persistedVar');
        $property->setAccessible(true);
        self::assertNull(
            $property->getValue($cloned)
        );
    }
}
