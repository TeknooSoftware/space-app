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

namespace Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account;

use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Contracts\Object\Account\AccountAwareInterface;
use Teknoo\East\Paas\Object\Account;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ReloadNamespace
{
    public function __invoke(
        ManagerInterface $manager,
        Account $account,
    ): self {
        $account->requireAccountNamespace(
            new class (
                $manager,
            ) implements AccountAwareInterface {
                public function __construct(
                    public ManagerInterface $manager,
                ) {
                }

                public function passAccountNamespace(
                    Account $account,
                    ?string $name,
                    ?string $namespace,
                    ?string $prefixNamespace,
                ): AccountAwareInterface {
                    $this->manager->updateWorkPlan([
                        'accountNamespace' => $namespace,
                    ]);

                    return $this;
                }
            }
        );

        return $this;
    }
}
