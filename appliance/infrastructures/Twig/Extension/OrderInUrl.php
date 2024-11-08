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

namespace Teknoo\Space\Infrastructures\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class OrderInUrl extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                name: 'app_order_in_url',
                callable: $this->orderInUrl(...)
            )
        ];
    }

    public function getName(): string
    {
        return 'app_order_in_url';
    }

    /**
     * @param array<string, string> $queryParams
     */
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
