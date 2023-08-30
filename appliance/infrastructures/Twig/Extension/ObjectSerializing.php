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

use Symfony\Component\Serializer\SerializerInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

use function get_parent_class;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ObjectSerializing extends AbstractExtension
{
    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function getFilters(): array
    {
        return array(
            new TwigFilter(
                'space_object_serialization',
                $this->serialize(...),
                [
                    'is_safe' => ['html', 'json'],
                ],
            )
        );
    }

    public function getName(): string
    {
        return 'space_object_serialization';
    }

    /**
     * @param object|array<string, mixed> $object
     * @param array<string, string[]> $context
     * @param array<string, mixed> $meta
     */
    public function serialize(
        object|array $object,
        array $context = [],
        string $format = 'json',
        array $meta = [],
    ): string {
        $computedMeta = [];
        if ($object instanceof IdentifiedObjectInterface) {
            $parentClass = $object::class;
            while (false !== ($tmp = get_parent_class($parentClass))) {
                $parentClass = $tmp;
            }

            $computedMeta['id'] = $object->getId();
            $computedMeta['@class'] = $parentClass;
        }

        return $this->serializer->serialize(
            data: [
                'meta' => array_merge(
                    $computedMeta,
                    $meta,
                ),
                'data' => $object,
            ],
            format: $format,
            context: $context,
        );
    }
}
