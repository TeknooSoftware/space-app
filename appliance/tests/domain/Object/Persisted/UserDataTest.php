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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Teknoo\East\Common\Object\Media;
use Teknoo\East\Common\Object\User;
use Teknoo\Space\Object\Persisted\UserData;

/**
 * Class UserDataTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Object\Persisted\UserData
 */
class UserDataTest extends TestCase
{
    private UserData $userData;

    private User|MockObject $user;

    private Media|MockObject $picture;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createMock(User::class);
        $this->picture = $this->createMock(Media::class);
        $this->userData = new UserData($this->user, $this->picture);
    }

    public function testSetUser(): void
    {
        $expected = $this->createMock(User::class);
        $property = (new ReflectionClass(UserData::class))
            ->getProperty('user');
        $property->setAccessible(true);
        $this->userData->setUser($expected);
        self::assertEquals($expected, $property->getValue($this->userData));
    }

    public function testSetPicture(): void
    {
        $expected = $this->createMock(Media::class);
        $property = (new ReflectionClass(UserData::class))
            ->getProperty('picture');
        $property->setAccessible(true);
        $this->userData->setPicture($expected);
        self::assertEquals($expected, $property->getValue($this->userData));
    }

    public function testGetPicture(): void
    {
        $expected = $this->createMock(Media::class);
        $property = (new ReflectionClass(UserData::class))
            ->getProperty('picture');
        $property->setAccessible(true);
        $property->setValue($this->userData, $expected);
        self::assertEquals($expected, $this->userData->getPicture());
    }

    public function testVisit(): void
    {
        $final = null;
        self::assertInstanceOf(
            UserData::class,
            $this->userData->visit([
                'picture' => function ($value) use (&$final) { $final = $value; },
                'foo' => fn() => self::fail('Must be not called'),
            ]),
        );
        self::assertInstanceOf(
            expected: Media::class,
            actual: $final,
        );
    }
}
