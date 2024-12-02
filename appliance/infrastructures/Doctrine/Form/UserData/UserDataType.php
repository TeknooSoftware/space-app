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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Infrastructures\Doctrine\Form\UserData;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\East\CommonBundle\Form\Type\MediaType;
use Teknoo\East\Common\Doctrine\Object\Media;
use Teknoo\East\Common\Doctrine\Writer\ODM\MediaWriter;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Object\Persisted\UserData;
use Throwable;
use Traversable;

use function array_map;
use function iterator_to_array;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class UserDataType extends AbstractType
{
    public function __construct(
        private MediaWriter $mediaWriter,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): self
    {
        $builder->add(
            'picture',
            MediaType::class,
            [
                'required' => false,
            ]
        );

        $builder->add(
            'removePicture',
            CheckboxType::class,
            [
                'required' => false,
                'mapped' => false,
                'false_values' => [
                    null,
                    '0',
                    '',
                ],
            ]
        );

        $builder->setDataMapper(new class () implements DataMapperInterface {
            /**
             * @param Traversable<string, FormInterface> $forms
             * @param ?\Teknoo\Space\Object\Persisted\UserData $data
             */
            public function mapDataToForms($data, $forms): void
            {
                if (!$data instanceof UserData) {
                    return;
                }

                $visitors = array_map(
                    fn (FormInterface $form): callable => $form->setData(...),
                    iterator_to_array($forms)
                );
                $data->visit($visitors);
            }

            /**
             * @param Traversable<string, FormInterface> $forms
             * @param ?UserData $data
             */
            public function mapFormsToData($forms, &$data): void
            {
                $forms = iterator_to_array($forms);
                $picture = $forms['picture']->getData();

                if ($data instanceof UserData && $picture instanceof Media) {
                    $data->setPicture($picture);
                }
            }
        });

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            static function (FormEvent $event) {
                /** @var array<string, array<string, string>> $data */
                $data = $event->getData();
                $data['picture']['name'] = 'profile-picture';
                $data['picture']['alternative'] = 'profile-picture';

                $event->setData($data);

                $form = $event->getForm();
                $userData = $form->getNormData();

                if ($userData instanceof UserData && !$userData->getPicture() instanceof Media) {
                    $userData->setPicture($media = new Media());
                    $form->get('picture')->setData($media);
                }
            }
        );

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $userData = $form->getNormData();
                if (!$userData instanceof UserData) {
                    return;
                }

                $media = $userData->getPicture();

                if (
                    $form->get('removePicture')->getViewData()
                    || (
                        $media instanceof Media
                        && empty($media->getId())
                        && empty($media->getMetadata()?->getLocalPath())
                    )
                ) {
                    if ($media instanceof Media && !empty($media->getId())) {
                        $this->mediaWriter->remove($media);
                    }

                    $userData->setPicture(null);

                    return;
                }

                if (
                    $media instanceof Media
                    && !empty($media->getMetadata()?->getLocalPath())
                ) {
                    /** @var Promise<Media, mixed, mixed> $promise */
                    $promise = new Promise(
                        static fn (Media $savedMedia) => $userData->setPicture($savedMedia),
                        static fn (Throwable $error) => $form->addError(new FormError($error->getMessage())),
                    );

                    $this->mediaWriter->save(
                        $media,
                        $promise
                    );
                }
            }
        );

        return $this;
    }

    public function configureOptions(OptionsResolver $resolver): self
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => UserData::class,
        ]);

        return $this;
    }
}
