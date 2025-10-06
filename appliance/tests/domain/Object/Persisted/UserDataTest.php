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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
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
 */
#[CoversClass(UserData::class)]
class UserDataTest extends TestCase
{
    private UserData $userData;

    private User&MockObject $user;

    private Media&MockObject $picture;

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
        $newUser = $this->createMock(User::class);
        $result = $this->userData->setUser($newUser);

        $this->assertInstanceOf(UserData::class, $result);
    }

    public function testSetPicture(): void
    {
        $newPicture = $this->createMock(Media::class);
        $result = $this->userData->setPicture($newPicture);

        $this->assertInstanceOf(UserData::class, $result);
    }

    public function testSetPictureWithNull(): void
    {
        $result = $this->userData->setPicture(null);

        $this->assertInstanceOf(UserData::class, $result);
        $this->assertNull($this->userData->getPicture());
    }

    public function testGetPicture(): void
    {
        $this->assertSame($this->picture, $this->userData->getPicture());
    }

    public function testGetPictureWithNull(): void
    {
        $userData = new UserData($this->user, null);

        $this->assertNull($userData->getPicture());
    }

    public function testConstructorWithNullPicture(): void
    {
        $userData = new UserData($this->user);

        $this->assertNull($userData->getPicture());
    }

    public function testVisit(): void
    {
        $final = null;
        $this->assertInstanceOf(
            UserData::class,
            $this->userData->visit([
                'picture' => function ($value) use (&$final): void {
                    $final = $value;
                },
                'foo' => fn () => self::fail('Must be not called'),
            ]),
        );
        $this->assertInstanceOf(
            expected: Media::class,
            actual: $final,
        );
    }
}
