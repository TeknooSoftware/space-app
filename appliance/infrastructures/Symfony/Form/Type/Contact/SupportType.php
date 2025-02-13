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

namespace Teknoo\Space\Infrastructures\Symfony\Form\Type\Contact;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Object\DTO\Contact;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SupportType extends AbstractType
{
    protected static function onSubmit(Contact $contact, ManagerInterface $manager): void
    {
        $manager->updateWorkPlan([
            Contact::class => $contact,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): self
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'fromName',
            TextType::class,
            [
                'required' => true,
                'label' => 'teknoo.space.form.contact.support.from_name',
            ],
        );

        $builder->add(
            'fromEmail',
            EmailType::class,
            [
                'required' => true,
                'label' => 'teknoo.space.form.contact.support.from_email',
            ],
        );

        $builder->add(
            'subject',
            TextType::class,
            [
                'required' => true,
                'label' => 'teknoo.space.form.contact.support.subject',
            ],
        );

        $builder->add(
            'message',
            TextareaType::class,
            [
                'required' => true,
                'label' => 'teknoo.space.form.contact.support.message',
            ],
        );

        $builder->add(
            'attachments',
            CollectionType::class,
            [
                'required' => true,
                'entry_type' => AttachmentType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'prototype_name' => '__name__',
                'label' => 'teknoo.space.form.contact.support.attachment',
            ],
        );

        if (empty($options['manager']) || !$options['manager'] instanceof ManagerInterface) {
            return $this;
        }

        $manager = $options['manager'];

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            static function (FormEvent $event) use ($manager) {
                /** @var \Teknoo\Space\Object\DTO\Contact $data */
                $data = $event->getData();
                self::onSubmit($data, $manager);
            }
        );

        return $this;
    }

    public function configureOptions(OptionsResolver $resolver): self
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Contact::class,
            'empty_data' => new Contact(),
            'manager' => null,
        ]);

        return $this;
    }
}
