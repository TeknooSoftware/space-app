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

namespace Teknoo\Space\Infrastructures\Symfony\Form\Type\Project;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\East\Paas\Infrastructures\Symfony\Form\Type\ProjectType;
use Teknoo\Space\Infrastructures\Symfony\Form\Type\ProjectMetadata\ProjectMetadataType;
use Teknoo\Space\Object\DTO\SpaceProject;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @extends AbstractType<SpaceProject>
 */
class SpaceProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'projectMetadata',
            ProjectMetadataType::class,
        );

        $builder->add(
            'project',
            ProjectType::class,
            [
                'allowEditingOfLocked' => $options['allowEditingOfLocked'] ?? false,
            ],
        );

        $builder->add(
            'addClusterName',
            HiddenType::class,
        );

        $builder->add(
            'addClusterEnv',
            HiddenType::class,
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => SpaceProject::class,
            'allowEditingOfLocked' => false,
        ]);

        $resolver->setrequired(['allowEditingOfLocked']);
        $resolver->setAllowedTypes('allowEditingOfLocked', ['bool']);
    }
}
