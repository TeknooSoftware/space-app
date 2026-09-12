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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Security\Voter;
namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Security\Provider;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Query\User\UserByEmailQuery;
use Teknoo\East\CommonBundle\Provider\Exception\MissingUserException;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Infrastructures\Symfony\Object\ApiKeysAuthUser;
use Teknoo\Space\Infrastructures\Symfony\Security\Provider\ApiKeysAuthenticatedUserProvider;
use Teknoo\Space\Object\Persisted\ApiKeysAuth;
use Teknoo\Space\Object\Persisted\ApiKeyToken;

/**
 * Class ApiKeysAuthenticatedUserProviderTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ApiKeysAuthenticatedUserProvider::class)]
class ApiKeysAuthenticatedUserProviderTest extends TestCase
{
    private ApiKeysAuthenticatedUserProvider $provider;

    private UserLoader&MockObject $loader;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = $this->createMock(UserLoader::class);
        $this->provider = new ApiKeysAuthenticatedUserProvider(
            $this->loader,
            (new DatesService())->setCurrentDate(new DateTimeImmutable('2024-01-01 12:00:00')),
        );
    }

    private function createUser(?ApiKeyToken $token): User
    {
        $user = (new User())->setEmail('foo@bar');
        $user->setAuthData([
            new ApiKeysAuth(null === $token ? [] : [$token]),
        ]);

        return $user;
    }

    private function expectLoaderToFetch(User $user): void
    {
        $this->loader
            ->expects($this->once())
            ->method('fetch')
            ->with($this->isInstanceOf(UserByEmailQuery::class))
            ->willReturnCallback(
                function (UserByEmailQuery $query, PromiseInterface $promise) use ($user): UserLoader {
                    $promise->success($user);

                    return $this->loader;
                },
            );
    }

    public function testLoadUserByIdentifierWithoutTokenName(): void
    {
        $this->loader->expects($this->never())->method('fetch');
        $this->expectException(UserNotFoundException::class);
        $this->provider->loadUserByIdentifier('foo@bar');
    }

    public function testLoadUserByUsernameWithEmptyTokenName(): void
    {
        $this->loader->expects($this->never())->method('fetch');
        $this->expectException(UserNotFoundException::class);
        $this->provider->loadUserByUsername(':foo@bar');
    }

    public function testLoadUserByIdentifierWithValidToken(): void
    {
        $token = new ApiKeyToken(
            name: 'bob',
            token: 'secret',
            tokenHash: 'hashed',
            expiresAt: new DateTimeImmutable('2025-01-01'),
        );
        $user = $this->createUser($token);
        $this->expectLoaderToFetch($user);

        $loaded = $this->provider->loadUserByIdentifier('bob:foo@bar');
        $this->assertInstanceOf(ApiKeysAuthUser::class, $loaded);
        $this->assertSame($user, $loaded->getWrappedUser());
        $this->assertSame('hashed', $loaded->getPassword());
    }

    public function testLoadUserByIdentifierWithExpiredToken(): void
    {
        $token = new ApiKeyToken(
            name: 'bob',
            token: 'secret',
            tokenHash: 'hashed',
            expiresAt: new DateTimeImmutable('2023-01-01'),
        );
        $this->expectLoaderToFetch($this->createUser($token));

        $this->expectException(UserNotFoundException::class);
        $this->provider->loadUserByIdentifier('bob:foo@bar');
    }

    public function testLoadUserByIdentifierWithMismatchedTokenName(): void
    {
        $token = new ApiKeyToken(
            name: 'alice',
            token: 'secret',
            tokenHash: 'hashed',
            expiresAt: new DateTimeImmutable('2025-01-01'),
        );
        $this->expectLoaderToFetch($this->createUser($token));

        $this->expectException(UserNotFoundException::class);
        $this->provider->loadUserByIdentifier('bob:foo@bar');
    }

    public function testLoadUserByIdentifierWithoutTokens(): void
    {
        $this->expectLoaderToFetch($this->createUser(null));

        $this->expectException(UserNotFoundException::class);
        $this->provider->loadUserByIdentifier('bob:foo@bar');
    }

    public function testRefreshUserWithApiKeysAuthUser(): void
    {
        $user = $this->createStub(ApiKeysAuthUser::class);
        $user->method('getUserIdentifier')->willReturn('foo@bar');

        $this->loader->expects($this->never())->method('fetch');
        $this->expectException(UserNotFoundException::class);
        $this->provider->refreshUser($user);
    }

    public function testRefreshUserWithOtherUser(): void
    {
        $user = $this->createStub(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn('foo@bar');

        $this->loader->expects($this->never())->method('fetch');
        $this->expectException(MissingUserException::class);
        $this->provider->refreshUser($user);
    }

    public function testSupportsClass(): void
    {
        $this->loader->expects($this->never())->method('fetch');
        $this->assertTrue($this->provider->supportsClass(ApiKeysAuthUser::class));
        $this->assertFalse($this->provider->supportsClass(User::class));
    }
}
