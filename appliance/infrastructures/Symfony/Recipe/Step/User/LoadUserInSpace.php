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

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Loader\Meta\SpaceAccountLoader;
use Teknoo\Space\Loader\Meta\SpaceUserLoader;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\DTO\SpaceUser;
use Teknoo\Space\Object\DTO\SpaceView;
use Teknoo\Space\Query\Account\FetchAccountFromUser;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LoadUserInSpace
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly SpaceUserLoader $spaceUserLoader,
        private readonly SpaceAccountLoader $spaceAccountLoader,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        ParametersBag $viewParameterBag,
    ): self {
        $token = $this->tokenStorage->getToken();
        if (!$token instanceof TokenInterface) {
            return $this;
        }

        $symfonyUser = $token->getUser();
        if (!$symfonyUser instanceof AbstractUser) {
            return $this;
        }

        $spaceView = new SpaceView();

        /** @var Promise<SpaceUser, mixed, mixed> $userPromise */
        $userPromise = new Promise(
            function (SpaceUser $spaceUser) use ($spaceView, $manager): void {
                $spaceView->user = $spaceUser;
                $manager->updateWorkPlan(
                    [
                        SpaceUser::class => $spaceUser,
                        User::class => $spaceUser->user,
                    ]
                );


                /** @var Promise<SpaceAccount, mixed, mixed> $accountPromise */
                $accountPromise = new Promise(
                    static function (SpaceAccount $spaceAccount) use ($spaceView, $manager): void {
                        $spaceView->account = $spaceAccount;
                        $manager->updateWorkPlan(
                            [
                                SpaceAccount::class => $spaceAccount,
                                Account::class => $spaceAccount->account,
                            ]
                        );
                    },
                );

                $this->spaceAccountLoader->fetch(
                    new FetchAccountFromUser($spaceUser->user),
                    $accountPromise
                );
            },
            fn (Throwable $error): ChefInterface => $manager->error($error),
        );

        $this->spaceUserLoader->load(
            $symfonyUser->getWrappedUser()->getId(),
            $userPromise
        );

        $viewParameterBag->set('space', $spaceView);

        return $this;
    }
}
