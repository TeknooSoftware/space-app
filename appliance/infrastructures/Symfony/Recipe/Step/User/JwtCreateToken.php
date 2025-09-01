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

namespace Teknoo\Space\Infrastructures\Symfony\Recipe\Step\User;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\Space\Contracts\Recipe\Step\User\JwtCreateTokenInterface;
use Teknoo\Space\Object\DTO\JWTConfiguration;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class JwtCreateToken implements JwtCreateTokenInterface
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function __invoke(
        ParametersBag $viewParameterBag,
        JWTConfiguration $configuration,
    ): JwtCreateTokenInterface {
        $token = $this->tokenStorage->getToken();
        if (!$token instanceof TokenInterface) {
            return $this;
        }

        $symfonyUser = $token->getUser();
        if (!$symfonyUser instanceof AbstractUser) {
            return $this;
        }

        $token = $this->jwtManager->createFromPayload(
            user: $symfonyUser,
            payload: [
                'exp' => $configuration->expirationDate?->getTimestamp(),
            ],
        );

        $viewParameterBag->set('jwtToken', $token);

        return $this;
    }
}
