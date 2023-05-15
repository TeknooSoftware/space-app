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

namespace Teknoo\Space\Infrastructures\Symfony\Form\Type\Job;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\Space\Object\DTO\JobVar;

use function is_array;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class JobVarType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'env_var';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): self
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'id',
            HiddenType::class,
        );

        $builder->add(
            'name',
            TextType::class,
            [
                'required' => true,
                'label' => 'teknoo.space.form.job.job_var.name',
            ],
        );

        $builder->add(
            'value',
            TextareaType::class,
            [
                'required' => false,
                'label' => 'teknoo.space.form.job.job_var.value',
            ],
        );

        $builder->add(
            'persisted',
            CheckboxType::class,
            [
                'required' => false,
                'label' => 'teknoo.space.form.job.job_var.persisted',
            ],
        );

        $builder->add(
            'secret',
            CheckboxType::class,
            [
                'required' => false,
                'label' => 'teknoo.space.form.job.job_var.secret',
            ],
        );

        $builder->add(
            'wasSecret',
            HiddenType::class,
            [
                'required' => false,
            ],
        );

        if (!empty($options['use_password_for_secret'])) {
            $builder->addEventListener(
                FormEvents::POST_SET_DATA,
                static function (FormEvent $formEvent): void {
                    $form = $formEvent->getForm();
                    $data = $formEvent->getData();

                    if ($data instanceof JobVar && true === $data->secret) {
                        $form->add(
                            'value',
                            PasswordType::class,
                            [
                                'required' => false,
                                'label' => 'teknoo.space.form.job.job_var.value',
                            ],
                        );
                    }
                }
            );

            $builder->addEventListener(
                FormEvents::PRE_SUBMIT,
                static function (FormEvent $formEvent): void {
                    $form = $formEvent->getForm();
                    $mData = $form->getNormData();
                    $data = $formEvent->getData();

                    if (
                        $mData instanceof JobVar
                        && true === $mData->secret
                        && is_array($data)
                        && empty($data['value'])
                    ) {
                        $data['value'] = $mData->value;
                        $formEvent->setData($data);
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
            'data_class' => JobVar::class,
            'use_password_for_secret' => false,
            'empty_data' => static function (FormInterface $form) {
                return new JobVar(
                    id: $form->get('id')->getData(),
                    name: $form->get('name')->getData(),
                    value: $form->get('value')->getData(),
                    persisted: $form->get('persisted')->getData(),
                    secret: $form->get('secret')->getData(),
                );
            },
        ]);

        return $this;
    }
}
