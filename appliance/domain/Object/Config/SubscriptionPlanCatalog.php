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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Object\Config;

use DomainException;
use IteratorAggregate;
use Traversable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements IteratorAggregate<SubscriptionPlan>
 */
class SubscriptionPlanCatalog implements IteratorAggregate
{
    /**
     * @param array<string, SubscriptionPlan> $subscriptionPlans
     */
    public function __construct(
        private readonly array $subscriptionPlans,
    ) {
    }

    public function getSubscriptionPlan(string $id): SubscriptionPlan
    {
        if (!isset($this->subscriptionPlans[$id])) {
            throw new DomainException("Subscription Plan {$id} is not available in the catalog");
        }

        return $this->subscriptionPlans[$id];
    }

    public function getIterator(): Traversable
    {
        yield from $this->subscriptionPlans;
    }
}
