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

namespace Teknoo\Space\Infrastructures\Symfony\Form\Type\User;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class UserType extends PasswordType
{
    /**
     * @param FormBuilderInterface<PasswordAuthenticatedUser|null> $builder
     * @param array<string, mixed> $options
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): self
    {
        $builder->add(
            'firstName',
            TextType::class,
            [
                'required' => true,
                'label' => 'teknoo.space.form.user.user.first_name',
            ],
        );

        $builder->add(
            'lastName',
            TextType::class,
            [
                'required' => true,
                'label' => 'teknoo.space.form.user.user.last_name',
            ],
        );

        $builder->add(
            'email',
            EmailType::class,
            [
                'required' => true,
                'label' => 'teknoo.space.form.user.user.email',
            ],
        );

        parent::buildForm($builder, $options);

        return $this;
    }
}
