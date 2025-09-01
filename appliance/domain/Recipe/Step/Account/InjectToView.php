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

namespace Teknoo\Space\Recipe\Step\Account;

use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\DTO\SpaceAccount;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class InjectToView
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __invoke(
        ManagerInterface $manager,
        ParametersBag $bag,
        ?SpaceAccount $spaceAccount = null,
        ?Account $account = null,
        bool $allowAccountSelection = false,
        array $parameters = [],
    ): self {
        if (null !== $spaceAccount) {
            $bag->set('spaceAccount', $spaceAccount);
            $parameters['accountId'] = $spaceAccount->account->getId();
        }

        if (null !== $account) {
            $bag->set('account', $account);
            $parameters['accountId'] = $account->getId();
        }

        if ($allowAccountSelection) {
            $manager->updateWorkPlan(['parameters' => $parameters]);
        }

        return $this;
    }
}
