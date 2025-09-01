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

namespace Teknoo\Space\Infrastructures\Twig\Extension;

use Twig\Attribute\AsTwigFunction;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class OrderInUrl
{
    /**
     * @param array<string, string> $queryParams
     */
    #[AsTwigFunction(name: 'app_order_in_url')]
    public function orderInUrl(
        array $queryParams,
        string $column,
    ): string {
        $currentDirection = 'ASC';
        if (!empty($queryParams['order']) && $column === $queryParams['order']) {
            if (!empty($queryParams['direction']) && 'ASC' === $queryParams['direction']) {
                $currentDirection = 'DESC';
            }
        }

        return '?order=' . $column . '&direction=' . $currentDirection;
    }
}
