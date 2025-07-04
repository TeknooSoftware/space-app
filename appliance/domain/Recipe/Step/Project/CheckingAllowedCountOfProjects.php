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

namespace Teknoo\Space\Recipe\Step\Project;

use OverflowException;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Loader\ProjectLoader;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Object\Config\SubscriptionPlan;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Query\Project\CountProjectsInAccount;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CheckingAllowedCountOfProjects
{
    public function __construct(
        private readonly ProjectLoader $projectLoader,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        SpaceAccount|Account|null $account = null,
        ?SubscriptionPlan $plan = null,
    ): self {
        $projectsAllowed = $plan?->projectsCountAllowed ?? 0;
        $errorMessage = 'Missing subscription plan for this account';
        if ($plan) {
            $errorMessage = "The plan {$plan->name} accepts only {$projectsAllowed} projects";
        }

        if ($account instanceof SpaceAccount) {
            $account = $account->account;
        }

        if (null === $account || 0 === $projectsAllowed) {
            return $this;
        }

        $projectsCountedPromise = new Promise(
            static function ($projectsCounted) use ($manager, $projectsAllowed, $errorMessage): void {
                if ($projectsCounted >= $projectsAllowed) {
                    $manager->error(
                        error: new OverflowException($errorMessage, 400,)
                    );
                }
            },
            static fn (Throwable $error)  => $manager->error($error)
        );

        $this->projectLoader->fetch(
            new CountProjectsInAccount(
                $account,
            ),
            $projectsCountedPromise,
        );

        return $this;
    }
}
