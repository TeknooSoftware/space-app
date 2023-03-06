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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Infrastructures\Symfony\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\East\CommonBundle\Form\Type\StoredPasswordType;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\User;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface<PasswordAuthenticatedUser> $builder
     * @param array<string, mixed> $options
     */
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

        $builder->add(
            'storedPassword',
            StoredPasswordType::class,
            [
                'mapped' => false,
            ],
        );


        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            static function (FormEvent $event) {
                /**
                 * @var User $user
                 */
                $user = $event->getData();
                $spForm = $event->getForm()->get('storedPassword');

                foreach ($user->getAuthData() as $authData) {
                    if (!$authData instanceof StoredPassword) {
                        continue;
                    }

                    $spForm->setData($authData);
                    return;
                }

                $authData = new StoredPassword();
                $spForm->setData($authData);
                $user->addAuthData($authData);
            }
        );

        return $this;
    }

    public function configureOptions(OptionsResolver $resolver): self
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => User::class,
        ]);

        return $this;
    }
}
