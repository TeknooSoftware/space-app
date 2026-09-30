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

use Symfony\Component\Form\FormView;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\CommonBundle\Twig\Extension\ApiObjectWithFormSerializing as BaseApiObjectWithFormSerializing;
use Twig\Attribute\AsTwigFilter;

/**
 * Alias of the East Common's Twig extension `ApiObjectWithFormSerializing`, to keep the Twig filter's name
 * `space_api_object_with_form_serialization` used by Space's templates.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ApiObjectWithFormSerializing extends BaseApiObjectWithFormSerializing
{
    /**
     * @param object|array<mixed, mixed> $object
     * @param array<string, mixed> $context
     * @param array<string, mixed> $meta
     */
    #[\Override]
    #[AsTwigFilter(name: 'space_api_object_with_form_serialization', isSafe: ['html', 'json', 'js'])]
    public function rendering(
        object|array $object,
        ?FormView $formView,
        array $context = [],
        string $format = 'json',
        array $meta = [],
        ?IdentifiedObjectInterface $parentObject = null,
    ): string {
        return parent::rendering($object, $formView, $context, $format, $meta, $parentObject);
    }
}
