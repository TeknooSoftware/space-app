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

use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Loader\ProjectLoader;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Object\Config\SubscriptionPlan;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Query\Project\CountProjectsInAccount;
use Throwable;

use function count;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class InjectStatus
{
    public function __construct(
        private readonly ProjectLoader $projectLoader,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        ParametersBag $bag,
        SpaceAccount $spaceAccount,
        ?SubscriptionPlan $plan = null,
    ): self {
        $projectsAllowed = $plan?->projectsCountAllowed ?? 0;
        $envsAllowed = $plan?->envsCountAllowed ?? 0;

        $envsCounted = count($spaceAccount?->environments ?? []);

        $projectsCountedPromise = new Promise(
            static fn ($projectsCount): int => $projectsCount,
            static fn (Throwable $error) => $manager->error($error)
        );

        $this->projectLoader->fetch(
            new CountProjectsInAccount(
                $spaceAccount->account,
            ),
            $projectsCountedPromise,
        );

        $projectsCounted = $projectsCountedPromise->fetchResult();

        $bag->set(
            'subscriptionStatus',
            [
                'planName' => $plan?->name ?? 'No plan',
                'quota' => $plan?->getQuotas() ?? [],
                'envsAllowed' => $envsAllowed,
                'envsCounted' => $envsCounted,
                'envsExceeding' => !empty($envsAllowed) && $envsCounted > $envsAllowed,
                'envsFull' => !empty($envsAllowed) && $envsCounted >= $envsAllowed,
                'projectsAllowed' => $projectsAllowed,
                'projectsCounted' => $projectsCounted,
                'projectsExceeding' => !empty($projectsAllowed) && $projectsCounted > $projectsAllowed,
                'projectsFull' => !empty($projectsAllowed) && $projectsCounted >= $projectsAllowed,
            ],
        );

        $bag->set('currentAccount', $spaceAccount);

        return $this;
    }
}
