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

namespace Teknoo\Space\Infrastructures\Symfony\Security\Provider;

use DateTimeInterface;
use ReflectionException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Teknoo\East\CommonBundle\Provider\Exception\MissingUserException;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Query\User\UserByEmailQuery;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Infrastructures\Symfony\Object\ApiKeysAuthUser;
use Teknoo\Space\Object\Persisted\ApiKeysAuth;
use Teknoo\Space\Object\Persisted\ApiKeyToken;

use function explode;
use function str_contains;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements UserProviderInterface<ApiKeysAuthUser>
 */
class ApiKeysAuthenticatedUserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly UserLoader $loader,
        private readonly DatesService $datesService,
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->fetchUserByUsername($identifier);
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->fetchUserByUsername($username);
    }

    protected function fetchUserByUsername(string $username): ApiKeysAuthUser
    {
        if (!str_contains($username, ':')) {
            throw new UserNotFoundException();
        }

        [$tokenName, $username] = explode(':', $username);

        if (empty($tokenName)) {
            throw new UserNotFoundException();
        }

        /** @var Promise<User, ApiKeysAuthUser, mixed> $promise */
        $promise = new Promise(function (User $user) use ($tokenName): ?ApiKeysAuthUser {
            $tokens = [];

            $this->datesService->passMeTheDate(
                static function (DateTimeInterface $now) use ($user, $tokenName, &$tokens): void {
                    foreach ($user->getAuthData() as $authData) {
                        if (
                            $authData instanceof ApiKeysAuth
                            && ($token = $authData->getToken($tokenName)) instanceof ApiKeyToken
                            && $token->getExpiresAt() > $now
                            && !$token->isExpired()
                        ) {
                            $tokens[] = $token;

                            break;
                        }
                    }
                }
            );

            if ([] === $tokens) {
                return null;
            }

            return new ApiKeysAuthUser($user, $tokens[0]);
        });

        $this->loader->fetch(
            new UserByEmailQuery($username),
            $promise,
        );

        $loadedUser = $promise->fetchResult();
        if (!$loadedUser instanceof ApiKeysAuthUser) {
            throw new UserNotFoundException();
        }

        return $loadedUser;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if ($user instanceof ApiKeysAuthUser) {
            return $this->fetchUserByUsername($user->getUserIdentifier());
        }

        throw new MissingUserException(
            "{$user->getUserIdentifier()} is not available with the provider " . self::class,
        );
    }

    /**
     * @param class-string<ApiKeysAuthUser> $class
     * @throws ReflectionException
     */
    public function supportsClass($class): bool
    {
        return ApiKeysAuthUser::class === $class;
    }
}
