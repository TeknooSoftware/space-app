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

namespace Teknoo\Space\Tests\Behat\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\Space\Tests\Behat\SpaceContext;

use function is_string;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
class MockChoiceType extends ChoiceType
{
    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'csrf_protection' => false,
            'class' => '',
            'query_builder' => '',
            // Like the DocumentType it replaces: the objects of the class, here those of the in-memory database
            'choices' => static function (Options $options): array {
                $class = $options['class'];
                if (!is_string($class) || '' === $class) {
                    return [];
                }

                return SpaceContext::current()->listObjects($class);
            },
            'choice_value' => static function (mixed $object): ?string {
                if ($object instanceof IdentifiedObjectInterface) {
                    return $object->getId();
                }

                return null;
            },
        ]);
    }
}
