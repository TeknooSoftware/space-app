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

use Teknoo\East\CommonBundle\Twig\Extension\ApiCollectionSerializing as BaseApiCollectionSerializing;
use Twig\Attribute\AsTwigFilter;

/**
 * Alias of the East Common's Twig extension `ApiCollectionSerializing`, to keep the Twig filter's name
 * `space_api_collection_serialization` used by Space's templates.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ApiCollectionSerializing extends BaseApiCollectionSerializing
{
    /**
     * @param iterable<mixed> $collection
     * @param array<string, mixed> $context
     * @param array<string, mixed> $meta
     */
    #[\Override]
    #[AsTwigFilter(name: 'space_api_collection_serialization', isSafe: ['html', 'json', 'js'])]
    public function serialize(
        iterable $collection,
        int $currentPage,
        int $countPages,
        array $context = [],
        string $format = 'json',
        array $meta = [],
    ): string {
        return parent::serialize($collection, $currentPage, $countPages, $context, $format, $meta);
    }
}
