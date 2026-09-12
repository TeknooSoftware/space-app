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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Recipe\Step\AccessControl;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\AccessControl\AbstractAccessControl;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\AccessControl\ObjectAccessControl;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\Space\Infrastructures\Symfony\Security\Exception\UnAuthorizedException;

/**
 * Class ObjectAccessControlTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AbstractAccessControl::class)]
#[CoversClass(ObjectAccessControl::class)]
class ObjectAccessControlTest extends TestCase
{
    private ObjectAccessControl $objectAccessControl;

    private AuthorizationCheckerInterface&Stub $authorizationChecker;

    private TokenStorageInterface&Stub $tokenStorage;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->authorizationChecker = $this->createStub(AuthorizationCheckerInterface::class);
        $this->tokenStorage = $this->createStub(TokenStorageInterface::class);

        $this->objectAccessControl = new ObjectAccessControl(
            $this->authorizationChecker,
            $this->tokenStorage,
        );
    }

    public function testInvoke(): void
    {
        $this->authorizationChecker
            ->method('isGranted')
            ->willReturn(true);

        $this->assertInstanceOf(
            ObjectAccessControl::class,
            ($this->objectAccessControl)(
                $this->createStub(ManagerInterface::class),
                $this->createStub(MessageInterface::class),
                $this->createStub(ObjectInterface::class),
            )
        );
    }

    public function testInvokeWithoutUserInToken(): void
    {
        $this->tokenStorage
            ->method('getToken')
            ->willReturn($this->createStub(TokenInterface::class));

        $this->authorizationChecker
            ->method('isGranted')
            ->willReturn(true);

        $manager = $this->createMock(ManagerInterface::class);
        $manager
            ->expects($this->never())
            ->method('updateWorkPlan');

        $this->assertInstanceOf(
            ObjectAccessControl::class,
            ($this->objectAccessControl)(
                $manager,
                $this->createStub(MessageInterface::class),
                $this->createStub(ObjectInterface::class),
            )
        );
    }

    public function testInvokeWithUserInToken(): void
    {
        $user = new User();
        $symfonyUser = $this->createStub(AbstractUser::class);
        $symfonyUser->method('getWrappedUser')->willReturn($user);
        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($symfonyUser);

        $this->tokenStorage
            ->method('getToken')
            ->willReturn($token);

        $this->authorizationChecker
            ->method('isGranted')
            ->willReturn(true);

        $manager = $this->createMock(ManagerInterface::class);
        $manager
            ->expects($this->once())
            ->method('updateWorkPlan')
            ->with([
                UserInterface::class => $symfonyUser,
                User::class => $user,
            ])
            ->willReturnSelf();

        $this->assertInstanceOf(
            ObjectAccessControl::class,
            ($this->objectAccessControl)(
                $manager,
                $this->createStub(MessageInterface::class),
                $this->createStub(ObjectInterface::class),
            )
        );
    }

    public function testInvokeWhenNotGranted(): void
    {
        $this->authorizationChecker
            ->method('isGranted')
            ->willReturn(false);

        $this->expectException(UnAuthorizedException::class);
        ($this->objectAccessControl)(
            $this->createStub(ManagerInterface::class),
            $this->createStub(MessageInterface::class),
            $this->createStub(ObjectInterface::class),
        );
    }
}
