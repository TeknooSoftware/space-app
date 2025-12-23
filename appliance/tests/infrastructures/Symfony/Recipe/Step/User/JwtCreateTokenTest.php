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

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Teknoo\East\Common\View\ParametersBag;
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

    private JWTTokenManagerInterface&Stub $jWTTokenManagerInterface;

    private DatesService&Stub $datesService;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->tokenStorage = $this->createStub(TokenStorageInterface::class);
        $this->jWTTokenManagerInterface = $this->createStub(JWTTokenManagerInterface::class);
        $this->datesService = $this->createStub(DatesService::class);
        $this->jwtCreateToken = new JwtCreateToken(
            $this->jWTTokenManagerInterface,
            $this->tokenStorage,
            $this->datesService,
            30,
        );
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            JwtCreateToken::class,
            ($this->jwtCreateToken)(
                $this->createStub(ParametersBag::class),
                $this->createStub(JWTConfiguration::class),
            )
        );
    }
}
