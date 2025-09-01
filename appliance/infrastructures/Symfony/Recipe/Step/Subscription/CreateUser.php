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

namespace Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Subscription;

use Symfony\Bundle\SecurityBundle\Security;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Contracts\Recipe\Step\Subscription\CreateUserInterface;
use Teknoo\Space\Infrastructures\Symfony\Object\PreAuthenticatedUser;
use Teknoo\Space\Object\DTO\SpaceSubscription;
use Teknoo\Space\Object\DTO\SpaceUser;
use Teknoo\Space\Writer\Meta\SpaceUserWriter;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CreateUser implements CreateUserInterface
{
    public function __construct(
        private readonly SpaceUserWriter $userWriter,
        private readonly Security $security,
    ) {
    }

    public function __invoke(
        SpaceSubscription $spaceSubscription,
        ManagerInterface $manager,
    ): CreateUserInterface {
        $spaceUser = $spaceSubscription->user;
        $user = $spaceUser->user;
        $user->setRoles(['ROLE_USER']);

        $this->userWriter->save($spaceUser);

        //Pre Auth user to compute ACL and execute voters later
        $this->security->login(
            user: new PreAuthenticatedUser($user),
            authenticatorName: 'form_login',
        );

        $manager->updateWorkPlan([
            SpaceUser::class => $spaceUser,
            User::class => $user,
        ]);

        return $this;
    }
}
