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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Recipe\Step\User;

use DateTimeImmutable;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\User\JwtCreateToken;
use Teknoo\Space\Object\DTO\JWTConfiguration;

/**
 * Class JwtCreateTokenTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(JwtCreateToken::class)]
class JwtCreateTokenTest extends TestCase
{
    private JwtCreateToken $jwtCreateToken;

    private TokenStorageInterface&Stub $tokenStorage;

    private JWTTokenManagerInterface&MockObject $jWTTokenManagerInterface;

    private DatesService $datesService;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->tokenStorage = $this->createStub(TokenStorageInterface::class);
        $this->jWTTokenManagerInterface = $this->createMock(JWTTokenManagerInterface::class);
        $this->datesService = (new DatesService())->setCurrentDate(new DateTimeImmutable('2024-01-01 00:00:00'));
        $this->jwtCreateToken = new JwtCreateToken(
            $this->jWTTokenManagerInterface,
            $this->tokenStorage,
            $this->datesService,
            30,
        );
    }

    public function testInvokeWithoutToken(): void
    {
        $this->jWTTokenManagerInterface
            ->expects($this->never())
            ->method('createFromPayload');

        $this->assertInstanceOf(
            JwtCreateToken::class,
            ($this->jwtCreateToken)(
                $this->createStub(ParametersBag::class),
                new JWTConfiguration(),
            )
        );
    }

    public function testInvokeWithoutUserInToken(): void
    {
        $this->tokenStorage
            ->method('getToken')
            ->willReturn($this->createStub(TokenInterface::class));

        $this->jWTTokenManagerInterface
            ->expects($this->never())
            ->method('createFromPayload');

        $this->assertInstanceOf(
            JwtCreateToken::class,
            ($this->jwtCreateToken)(
                $this->createStub(ParametersBag::class),
                new JWTConfiguration(),
            )
        );
    }

    private function prepareToken(): AbstractUser
    {
        $symfonyUser = $this->createStub(AbstractUser::class);
        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($symfonyUser);

        $this->tokenStorage
            ->method('getToken')
            ->willReturn($token);

        return $symfonyUser;
    }

    public function testInvokeWithoutExpirationDate(): void
    {
        $symfonyUser = $this->prepareToken();

        $this->jWTTokenManagerInterface
            ->expects($this->once())
            ->method('createFromPayload')
            ->with(
                $symfonyUser,
                ['exp' => (new DateTimeImmutable('2024-01-31 00:00:00'))->getTimestamp()],
            )
            ->willReturn('jwt-token');

        $bag = $this->createMock(ParametersBag::class);
        $bag
            ->expects($this->once())
            ->method('set')
            ->with('jwtToken', 'jwt-token')
            ->willReturnSelf();

        $this->assertInstanceOf(
            JwtCreateToken::class,
            ($this->jwtCreateToken)(
                $bag,
                new JWTConfiguration(expirationDate: null),
            )
        );
    }

    public function testInvokeWithExpirationDate(): void
    {
        $symfonyUser = $this->prepareToken();

        $this->jWTTokenManagerInterface
            ->expects($this->once())
            ->method('createFromPayload')
            ->with(
                $symfonyUser,
                ['exp' => (new DateTimeImmutable('2024-01-02 00:00:00'))->getTimestamp()],
            )
            ->willReturn('jwt-token');

        $bag = $this->createMock(ParametersBag::class);
        $bag
            ->expects($this->once())
            ->method('set')
            ->with('jwtToken', 'jwt-token')
            ->willReturnSelf();

        $this->assertInstanceOf(
            JwtCreateToken::class,
            ($this->jwtCreateToken)(
                $bag,
                new JWTConfiguration(expirationDate: new DateTimeImmutable('2024-01-02 00:00:00')),
            )
        );
    }
}
