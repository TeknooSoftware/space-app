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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Recipe\Step\Subscription;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
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
 * @copyright Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Subscription\LoginUser
 */
class LoginUserTest extends TestCase
{
    private LoginUser $loginUser;

    private LoginLinkHandlerInterface|MockObject $loginLinkHandler;

    private ResponseFactoryInterface|MockObject $responseFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loginLinkHandler = $this->createMock(LoginLinkHandlerInterface::class);
        $this->loginUser = new LoginUser($this->loginLinkHandler);
        $this->loginUser->setRouter($this->createMock(UrlGeneratorInterface::class));
        $this->loginUser->setResponseFactory(
            $this->responseFactory = $this->createMock(ResponseFactoryInterface::class)
        );
    }

    public function testInvoke(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::any())
            ->method('withHeader')
            ->willReturnSelf();

        $this->responseFactory->expects(self::any())
            ->method('createResponse')
            ->willReturn($response);

        $user = $this->createMock(User::class);
        $user->expects(self::any())
            ->method('getAuthData')
            ->willReturn([
                $this->createMock(StoredPassword::class),
            ]);

        self::assertInstanceOf(
            LoginUser::class,
            ($this->loginUser)(
                $user,
                $this->createMock(ManagerInterface::class),
                $this->createMock(ClientInterface::class),
            )
        );
    }
}
