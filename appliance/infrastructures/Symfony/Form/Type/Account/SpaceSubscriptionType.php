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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Infrastructures\Symfony\Form\Type\Account;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Infrastructures\Symfony\Form\Type\User\SpaceUserType;
use Teknoo\Space\Infrastructures\Symfony\Service\Account\CodeGenerator;
use Teknoo\Space\Object\DTO\SpaceSubscription as SpaceSubscriptionDTO;
use Throwable;

use function trim;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SpaceSubscriptionType extends AbstractType
{
    public function __construct(
        private CodeGenerator $codeGenerator,
        private bool $enableCodeRestriction = true,
    ) {
    }

    public function setEnableCodeRestriction(bool $enableCodeRestriction): SpaceSubscriptionType
    {
        $this->enableCodeRestriction = $enableCodeRestriction;

        return $this;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): self
    {
        $builder->add(
            'user',
            SpaceUserType::class,
        );

        $builder->add(
            'account',
            SpaceAccountType::class,
            [
                'doctrine_type' => $options['doctrine_type'] ?? '',
            ]
        );

        if ($this->enableCodeRestriction) {
            $builder->add(
                'code',
                TextType::class,
                [
                    'required' => true,
                    'label' => 'teknoo.space.form.account.subscription.code',
                ],
            );

            $builder->addEventListener(
                FormEvents::PRE_SUBMIT,
                function (FormEvent $event) {
                    $form = $event->getForm();
                    /** @var array{code: ?string, account: ?array{account: ?array{name: ?string}}} $data */
                    $data = $event->getData();

                    /** @var Promise<string, string|Throwable, mixed> $promise */
                    $promise = new Promise(
                        fn (string $code) => $code,
                        fn (Throwable $error) => $error,
                    );

                    $this->codeGenerator->verify(
                        trim($data['account']['account']['name'] ?? ''),
                        trim($data['code'] ?? ''),
                        $promise
                    );

                    if (($error = $promise->fetchResult()) instanceof Throwable) {
                        $form->addError(new FormError($error->getMessage()));
                    }
                }
            );
        }

        return $this;
    }

    public function configureOptions(OptionsResolver $resolver): self
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => SpaceSubscriptionDTO::class,
        ]);

        $resolver->setRequired(['doctrine_type']);
        $resolver->setAllowedTypes('doctrine_type', 'string');

        return $this;
    }
}
