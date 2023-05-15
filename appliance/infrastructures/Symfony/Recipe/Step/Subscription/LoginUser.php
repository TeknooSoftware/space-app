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
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Subscription;

use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\User;
use Teknoo\East\FoundationBundle\EndPoint\RoutingTrait;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\EndPoint\RedirectingInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Contracts\Recipe\Step\Subscription\LoginUserInterface;
use Teknoo\Space\Infrastructures\Symfony\Security\Exception\AutoLoginUnavailableException;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LoginUser implements LoginUserInterface, RedirectingInterface
{
    use RoutingTrait;

    public function __construct(
        private LoginLinkHandlerInterface $loginLinkHandler,
    ) {
    }

    public function __invoke(
        User $user,
        ManagerInterface $manager,
        ClientInterface $client
    ): LoginUserInterface {
        $storedPassword = null;
        $authData = null;
        foreach ($user->getAuthData() as $authData) {
            if ($authData instanceof StoredPassword) {
                $storedPassword = $authData;
                break;
            }
        }

        if (null === $storedPassword) {
            throw new AutoLoginUnavailableException(
                message: 'teknoo.space.error.authentification.autologin_not_available',
                code: 403,
            );
        }

        $linkDetails = $this->loginLinkHandler->createLoginLink(
            new PasswordAuthenticatedUser($user, $storedPassword)
        );

        $manager->updateWorkPlan([
            LoginLinkDetails::class => $linkDetails,
        ]);

        $this->redirect($client, $linkDetails->getUrl());

        $manager->stop();

        return $this;
    }
}
