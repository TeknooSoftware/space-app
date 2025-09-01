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

namespace Teknoo\Space\Infrastructures\Symfony\Form\Type\Job;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\East\CommonBundle\Contracts\Form\FormApiAwareInterface;
use Teknoo\Space\Object\DTO\JobVar;
use Teknoo\Space\Object\DTO\NewJob;

use function array_merge;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @extends AbstractType<NewJob>
 */
class ApiNewJobType extends AbstractType implements FormApiAwareInterface
{
    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'new_job';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): self
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'envName',
            ChoiceType::class,
            [
                'required' => true,
                'choices' => $options['environmentsList'],
                'label' => 'teknoo.space.form.job.new_job.environment',
            ],
        );

        $builder->add(
            'variables',
            CollectionType::class,
            [
                'entry_type' => JobVarType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'prototype_data' => new JobVar(
                    canPersist: true,
                ),
                'entry_options' => [
                    'usePasswordForSecret' => true,
                ]
            ],
        );

        if (!empty($options['api'])) {
            $builder->addEventListener(
                FormEvents::PRE_SUBMIT,
                static function (FormEvent $formEvent): void {
                    $form = $formEvent->getForm();
                    $mData = $form->getNormData();
                    /** @var array<string, array<string, array<string, mixed>>> $data */
                    $data = $formEvent->getData();

                    if (!$mData instanceof NewJob) {
                        return;
                    }

                    $initialVariables = [];
                    foreach ($mData->variables as $key => $var) {
                        $initialVariables[$key] = [
                            'id' => $var->getId(),
                            'name' => $var->name,
                            'value' => $data['variables'][$var->name]['value'] ?? $var->value,
                            'persisted' => $data['variables'][$var->name]['persisted'] ?? $var->persisted,
                            'secret' => $data['variables'][$var->name]['secret'] ?? $var->isSecret(),
                            'wasSecret' => $var->isSecret(),
                            'encryptionAlgorithm' => $var->getEncryptionAlgorithm(),
                        ];
                    }

                    $data['variables'] = array_merge($data['variables'] ?? [], $initialVariables);

                    $formEvent->setData($data);
                }
            );
        }

        return $this;
    }

    public function configureOptions(OptionsResolver $resolver): self
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => NewJob::class,
            'empty_data' => static fn (FormInterface $form): NewJob => new NewJob(
                $form->get('newJobId')->getData(),
                $form->get('variables')->getData(),
                $form->get('projectId')->getData(),
                $form->get('accountId')->getData(),
                $form->get('envName')->getData(),
            ),
            'environmentsList' => [],
            'api' => null,
        ]);

        $resolver->setAllowedTypes('api', ['null', 'string']);
        $resolver->setAllowedTypes('environmentsList', ['array']);

        return $this;
    }
}
