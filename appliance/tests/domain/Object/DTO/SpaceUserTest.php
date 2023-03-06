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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
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
use Teknoo\East\Common\Object\User;
use Teknoo\Space\Object\DTO\SpaceUser;
use Teknoo\Space\Object\Persisted\UserData;

/**
 * Class SpaceUserTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Object\DTO\SpaceUser
 */
class SpaceUserTest extends TestCase
{
    private SpaceUser $spaceUser;

    private User|MockObject $user;

    private UserData|MockObject $userData;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createMock(User::class);
        $this->userData = $this->createMock(UserData::class);
        $this->spaceUser = new SpaceUser($this->user, $this->userData);
    }

    public function testGetId(): void
    {
        $this->user
            ->expects(self::any())
            ->method('getId')
            ->willReturn('foo');

        self::assertEquals(
            'foo',
            $this->spaceUser->getId()
        );
    }

    public function testToString(): void
    {
        $this->user
            ->expects(self::any())
            ->method('__toString')
            ->willReturn('foo');

        self::assertEquals(
            'foo',
            (string) $this->spaceUser,
        );
    }
}
