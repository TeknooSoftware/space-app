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

namespace Teknoo\Space\Infrastructures\Symfony\Form\Type\Contact;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Teknoo\Space\Object\DTO\ContactAttachment;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @extends AbstractType<ContactAttachment>
 */
class AttachmentType extends AbstractType
{
    public function __construct(
        private readonly int $mailMaxFileSize = 204800,
        private readonly array $mailAllowedMimesTypes = ['text/plain', 'image/jpeg', 'image/png', 'image/gif'],
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): self
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'file',
            FileType::class,
            [
                'required' => false,
                'label' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => $this->mailMaxFileSize,
                        'mimeTypes' => $this->mailAllowedMimesTypes,
                        'mimeTypesMessage' => 'teknoo.space.error.contact.invalid_file_type',
                        'maxSizeMessage' => 'teknoo.space.error.contact.file_too_large',
                    ])
                ]
            ],
        );

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            static function (FormEvent $event): void {
                $form = $event->getForm();
                if (!$form->isValid()) {
                    return;
                }

                /** @var ContactAttachment $data */
                $data = $event->getData();

                $file = $form->get('file')->getData();
                if (!$file instanceof UploadedFile) {
                    return;
                }

                $data->fileName = $file->getClientOriginalName();
                $data->mimeType = (string) $file->getMimeType();
                $data->fileLength = $file->getFileInfo()->getSize();
                $data->fileContent = $file->getContent();
            }
        );

        return $this;
    }

    public function configureOptions(OptionsResolver $resolver): self
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => ContactAttachment::class,
            'empty_data' => new ContactAttachment(),
        ]);

        return $this;
    }
}
