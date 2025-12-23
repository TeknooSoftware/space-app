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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Recipe\Step\Subscription;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Subscription\LoginUser;

/**
 * Class LoginUserTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(LoginUser::class)]
class LoginUserTest extends TestCase
{
    private LoginUser $loginUser;

    private LoginLinkHandlerInterface&Stub $loginLinkHandler;

    private Security&Stub $security;

    private ResponseFactoryInterface&Stub $responseFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loginLinkHandler = $this->createStub(LoginLinkHandlerInterface::class);
        $this->security = $this->createStub(Security::class);
        $this->loginUser = new LoginUser(
            $this->loginLinkHandler,
            $this->security,
        );
        $this->loginUser->setRouter($this->createStub(UrlGeneratorInterface::class));
        $this->loginUser->setResponseFactory(
            $this->responseFactory = $this->createStub(ResponseFactoryInterface::class)
        );
    }

    public function testInvoke(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response
            ->method('withHeader')
            ->willReturnSelf();

        $this->responseFactory
            ->method('createResponse')
            ->willReturn($response);

        $user = $this->createStub(User::class);
        $user
            ->method('getAuthData')
            ->willReturn([
                $this->createStub(StoredPassword::class),
            ]);

        $this->assertInstanceOf(
            LoginUser::class,
            ($this->loginUser)(
                $user,
                $this->createStub(ManagerInterface::class),
                $this->createStub(ClientInterface::class),
            )
        );
    }
}
