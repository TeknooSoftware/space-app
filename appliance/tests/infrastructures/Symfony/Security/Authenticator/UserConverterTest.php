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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Security\Authenticator;

use League\OAuth2\Client\Provider\GenericResourceOwner;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Infrastructures\Symfony\Security\Authenticator\UserConverter;

/**
 * Class AccountVoterTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(UserConverter::class)]
class UserConverterTest extends TestCase
{
    private UserConverter $userConverter;

    private ?GenericResourceOwner $owner = null;

    private ?PromiseInterface $promise = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userConverter = new UserConverter();

        $this->owner = $this->createMock(GenericResourceOwner::class);
        $this->owner->expects($this->any())->method('toArray')->willReturn([
            'email' => 'foo@bar',
            'lastname' => 'foo',
            'firstname' => 'bar',
        ]);

        $this->promise = $this->createMock(PromiseInterface::class);
    }

    public function testExtractEmail(): void
    {
        self::assertInstanceOf(
            UserConverter::class,
            $this->userConverter->extractEmail(
                $this->owner,
                $this->promise
            )
        );
    }

    public function testConvertToUser(): void
    {
        self::assertInstanceOf(
            UserConverter::class,
            $this->userConverter->convertToUser(
                $this->owner,
                $this->promise
            )
        );
    }
}
