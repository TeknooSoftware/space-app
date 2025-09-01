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

namespace Teknoo\Space\Object\Config;

use Teknoo\East\Paas\Object\AccountQuota;

use function is_string;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SubscriptionPlan
{
    /** @var iterable<AccountQuota> */
    private readonly iterable $quotas;

    /**
     * @var string[]
     */
    private readonly array $clusters;

    /**
     * @param array<array{category: string, type: string, capacity: string, requires: string}> $quotas
     * @param string|string[] $clusters
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        array $quotas,
        public ?int $envsCountAllowed = 1,
        public ?int $projectsCountAllowed = 1,
        string|array $clusters = [],
    ) {
        $final = [];
        foreach ($quotas as $def) {
            $final[$def['type']] = AccountQuota::create($def);
        }

        $this->quotas = $final;
        if (is_string($clusters)) {
            $this->clusters = [$clusters];
        } else {
            $this->clusters = $clusters;
        }
    }

    /**
     * @return string[]
     */
    public function getClusters(): array
    {
        return $this->clusters;
    }

    /**
     * @return iterable<AccountQuota>
     */
    public function getQuotas(): iterable
    {
        return $this->quotas;
    }
}
