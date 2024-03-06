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
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Object\Config;

use Teknoo\East\Paas\Object\AccountQuota;

use function array_map;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SubscriptionPlan
{
    /** @var iterable<AccountQuota> */
    private readonly iterable $quotas;

    /**
     * @param array<array{category: string, type: string, capacity: string, requires: string}> $quotas
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        array $quotas,
    ) {
        $final = [];
        foreach ($quotas as $def) {
            $final[$def['type']] = AccountQuota::create($def);
        }

        $this->quotas = $final;
    }

    /**
     * @return iterable<AccountQuota>
     */
    public function getQuotas(): iterable
    {
        return $this->quotas;
    }
}
