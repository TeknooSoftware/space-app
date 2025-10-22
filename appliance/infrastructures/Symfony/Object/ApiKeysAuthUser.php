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

namespace Teknoo\Space\Infrastructures\Symfony\Object;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherAwareInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Teknoo\East\Common\Object\User as BaseUser;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\Space\Object\Persisted\ApiKeyToken;

/**
 * Symfony user implentation to wrap a East Common user instance authenticated via a token.
 * Authenticating data are stored into a ApiKeysAuth instance.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * * @author      Richard Déloge <richard@teknoo.software>
 */
class ApiKeysAuthUser extends AbstractUser implements PasswordAuthenticatedUserInterface, PasswordHasherAwareInterface
{
    public function __construct(
        BaseUser $user,
        protected ApiKeyToken $token,
    ) {
        parent::__construct($user);
    }

    public function getPassword(): string
    {
        return $this->token->getTokenHash();
    }

    #[\Override]
    public function eraseCredentials(): void
    {
        parent::eraseCredentials();
    }

    public function getPasswordHasherName(): ?string
    {
        return ApiKeysAuthUser::class;
    }
}
