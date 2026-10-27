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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\East\CommonBundle\Contracts\Form\FormApiAwareInterface;
use Teknoo\East\CommonBundle\Form\Type\ApiUserType;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Infrastructures\Doctrine\Form\UserData\UserDataType;
use Teknoo\Space\Loader\Meta\SpaceAccountLoader;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\DTO\SpaceUser;
use Teknoo\Space\Query\Account\FetchAccountFromUser;

use function count;
use function in_array;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @extends AbstractType<SpaceUser>
 */
class AdminSpaceUserType extends AbstractType implements FormApiAwareInterface
{
    public function __construct(
        private readonly SpaceAccountLoader $spaceAccountLoader,
        private readonly string $adminRoleName,
        private readonly string $pendingRoleName,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        //Without the password: an administrator of Space can not set the password of an other user
        $builder->add(
            'user',
            ApiUserType::class,
        );

        //The roles of East Common, with the role of a user attached to no account
        $builder->get('user')->add(
            'roles',
            ChoiceType::class,
            [
                'required' => true,
                'multiple' => true,
                'choices' => [
                    'user' => 'ROLE_USER',
                    'admin' => $this->adminRoleName,
                    'pending' => $this->pendingRoleName,
                ],
            ],
        );

        $builder->add(
            'userData',
            UserDataType::class,
        );

        //An administrator of Space is never a user of an account: a user of an account can not get the admin role.
        //The pending role is alone, and only for a user attached to no account
        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event): void {
                $spaceUser = $event->getData();
                if (!$spaceUser instanceof SpaceUser) {
                    return;
                }

                $user = $spaceUser->user;
                $roles = (array) $user->getRoles();
                $isPending = in_array($this->pendingRoleName, $roles, true);
                if ($isPending && 1 < count($roles)) {
                    $event->getForm()->get('user')->get('roles')->addError(
                        new FormError('teknoo.space.error.space_user.pending_alone'),
                    );

                    return;
                }

                if (empty($user->getId()) || (!$isPending && !in_array($this->adminRoleName, $roles, true))) {
                    return;
                }

                $rolesField = $event->getForm()->get('user')->get('roles');
                $error = 'teknoo.space.error.space_user.admin_in_account';
                if ($isPending) {
                    $error = 'teknoo.space.error.space_user.pending_in_account';
                }

                /** @var Promise<SpaceAccount, mixed, mixed> $promise */
                $promise = new Promise(
                    static function () use ($rolesField, $error): void {
                        $rolesField->addError(new FormError($error));
                    },
                    static function (): void {
                    },
                );

                $this->spaceAccountLoader->fetch(new FetchAccountFromUser($user), $promise);
            },
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => SpaceUser::class,
            'api' => null,
        ]);

        $resolver->setAllowedTypes('api', ['null', 'string']);
    }
}
