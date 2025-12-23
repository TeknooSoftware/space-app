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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\ApiKey;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Object\DTO\SpaceUser;
use Teknoo\Space\Object\Persisted\ApiKeysAuth;
use Teknoo\Space\Recipe\Step\ApiKey\RemoveKey;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(RemoveKey::class)]
class RemoveKeyTest extends TestCase
{
    public function testInvokeWithExistingApiKeysAuth(): void
    {
        $apiKeys = $this->createMock(ApiKeysAuth::class);
        $apiKeys->expects($this->once())
            ->method('removeToken')
            ->with('to-remove')
            ->willReturnSelf();

        $user = $this->createStub(User::class);
        $user->method('getOneAuthData')
            ->with(ApiKeysAuth::class)
            ->willReturn($apiKeys);

        $spaceUser = new SpaceUser($user);
        $step = new RemoveKey();

        $result = $step(
            manager: $this->createStub(ManagerInterface::class),
            user: $spaceUser,
            tokenName: 'to-remove',
        );

        $this->assertSame($step, $result);
    }

    public function testInvokeWithoutExistingApiKeysAuth(): void
    {
        $user = $this->createStub(User::class);
        $user->method('getOneAuthData')
            ->with(ApiKeysAuth::class)
            ->willReturn(null);

        $spaceUser = new SpaceUser($user);
        $step = new RemoveKey();

        // Just ensure it does not error and returns itself when there is no ApiKeysAuth yet
        $result = $step(
            manager: $this->createStub(ManagerInterface::class),
            user: $spaceUser,
            tokenName: 'whatever',
        );

        $this->assertSame($step, $result);
    }
}
