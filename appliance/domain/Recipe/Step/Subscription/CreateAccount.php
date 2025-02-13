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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Recipe\Step\Subscription;

use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Contracts\Recipe\Step\Subscription\CreateAccountInterface;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\DTO\SpaceSubscription;
use Teknoo\Space\Writer\Meta\SpaceAccountWriter;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CreateAccount implements CreateAccountInterface
{
    public function __construct(
        private SpaceAccountWriter $accountWriter,
    ) {
    }

    public function __invoke(
        SpaceSubscription $spaceSubscription,
        User $user,
        ManagerInterface $manager,
    ): CreateAccountInterface {
        $spaceAccount = $spaceSubscription->account;
        $account = $spaceAccount->account;
        $account->setUsers([$user]);

        $this->accountWriter->save($spaceAccount);

        $manager->updateWorkPlan([
            SpaceAccount::class => $spaceAccount,
            Account::class => $account,
            ObjectInterface::class => $account,
        ]);

        return $this;
    }
}
