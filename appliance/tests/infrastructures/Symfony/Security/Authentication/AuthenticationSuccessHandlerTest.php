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
 * @link        https://teknoo.software/applications/space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Security\Authentication;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Teknoo\Space\Infrastructures\Symfony\Security\Authentication\AuthenticationSuccessHandler;

/**
 * Class AccountVoterTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AuthenticationSuccessHandler::class)]
class AuthenticationSuccessHandlerTest extends TestCase
{
    private AuthenticationSuccessHandler $handler;

    private ?UrlGeneratorInterface $urlGenerator = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->urlGenerator->expects($this->any())
            ->method('generate')
            ->willReturn('/foo');

        $this->handler = new AuthenticationSuccessHandler(
            $this->urlGenerator,
            'space_dashboard',
            'space_change_password'
        );
    }

    public function testOnAuthenticationSuccess(): void
    {
        self::assertInstanceOf(
            RedirectResponse::class,
            $this->handler->onAuthenticationSuccess(
                $this->createMock(Request::class),
                $this->createMock(TokenInterface::class),
            )
        );
    }
}
