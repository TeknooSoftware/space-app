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

namespace Teknoo\Space\Recipe\Step\AccountEnvironment;

use OverflowException;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Object\Config\SubscriptionPlan;
use Teknoo\Space\Object\DTO\SpaceAccount;

use function count;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CheckingAllowedCountOfEnvs
{
    public function __invoke(
        ManagerInterface $manager,
        SpaceAccount $spaceAccount,
        ?SubscriptionPlan $plan = null,
    ): self {
        $envsAllowed = $plan?->envsCountAllowed ?? 0;
        $errorMessage = 'Missing subscription plan for this account';
        if ($plan) {
            $errorMessage = "The plan {$plan->name} accepts only {$envsAllowed} environments";
        }

        if (
            0 < $envsAllowed
            && !empty($spaceAccount->environments)
            && count($spaceAccount->environments) > $envsAllowed
        ) {
            $manager->error(
                error: new OverflowException($errorMessage, 400,)
            );
        }

        return $this;
    }
}
