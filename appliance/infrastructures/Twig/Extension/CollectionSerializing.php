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

namespace Teknoo\Space\Infrastructures\Twig\Extension;

use Countable;
use Symfony\Component\Serializer\SerializerInterface;
use Traversable;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

use function array_merge;
use function count;
use function iterator_to_array;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CollectionSerializing extends AbstractExtension
{
    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function getFilters(): array
    {
        return array(
            new TwigFilter(
                name: 'space_collection_serialization',
                callable: $this->serialize(...),
                options: [
                    'is_safe' => ['html', 'json', 'js'],
                ],
            )
        );
    }

    public function getName(): string
    {
        return 'space_collection_serialization';
    }

    /**
     * @param iterable<mixed> $collection
     * @param array<string, string[]> $context
     * @param array<string, mixed> $meta
     */
    public function serialize(
        iterable $collection,
        int $currentPage,
        int $countPages,
        array $context = [],
        string $format = 'json',
        array $meta = [],
    ): string {
        $arrayCollection = $collection;
        if ($arrayCollection instanceof Traversable) {
            $arrayCollection = iterator_to_array($arrayCollection);
        }

        $count = 0;
        if ($collection instanceof Countable) {
            $count = $collection->count();
        } else {
            $count = count($arrayCollection);
        }

        return $this->serializer->serialize(
            data: [
                'meta' => array_merge(
                    [
                        'totalPages' => $countPages,
                        'page' => $currentPage,
                        'count' => $count,
                    ],
                    $meta,
                ),
                'data' => $arrayCollection,
            ],
            format: $format,
            context: $context,
        );
    }
}
