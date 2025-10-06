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
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Normalizer\EastNormalizerInterface;
use Teknoo\Space\Object\DTO\SpaceUser;
use Teknoo\Space\Object\Persisted\UserData;

/**
 * Class SpaceUserTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SpaceUser::class)]
class SpaceUserTest extends TestCase
{
    private SpaceUser $spaceUser;

    private User&MockObject $user;

    private UserData&MockObject $userData;

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
            ->method('getId')
            ->willReturn('foo');

        $this->assertEquals(
            'foo',
            $this->spaceUser->getId()
        );
    }

    public function testToString(): void
    {
        $this->user
            ->method('__toString')
            ->willReturn('foo');

        $this->assertEquals(
            'foo',
            (string) $this->spaceUser,
        );
    }

    public function testConstructorWithNullUserData(): void
    {
        $user = $this->createMock(User::class);
        $spaceUser = new SpaceUser($user, null);

        $this->assertSame($user, $spaceUser->user);
        $this->assertInstanceOf(UserData::class, $spaceUser->userData);
    }

    public function testConstructorWithDefaultUser(): void
    {
        $spaceUser = new SpaceUser();

        $this->assertInstanceOf(User::class, $spaceUser->user);
        $this->assertInstanceOf(UserData::class, $spaceUser->userData);
    }

    public function testExportToMeData(): void
    {
        $normalizer = $this->createMock(EastNormalizerInterface::class);
        $normalizer->expects($this->once())
            ->method('injectData')
            ->with($this->isArray())
            ->willReturnSelf();

        $result = $this->spaceUser->exportToMeData($normalizer);

        $this->assertInstanceOf(SpaceUser::class, $result);
        $this->assertSame($this->spaceUser, $result);
    }

    public function testExportToMeDataWithContext(): void
    {
        $normalizer = $this->createMock(EastNormalizerInterface::class);
        $normalizer->expects($this->once())
            ->method('injectData')
            ->with($this->isArray())
            ->willReturnSelf();

        $context = ['groups' => ['api', 'crud']];
        $result = $this->spaceUser->exportToMeData($normalizer, $context);

        $this->assertInstanceOf(SpaceUser::class, $result);
        $this->assertSame($this->spaceUser, $result);
    }
}
