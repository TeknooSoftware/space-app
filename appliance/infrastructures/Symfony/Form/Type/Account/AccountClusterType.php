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

namespace Teknoo\Space\Infrastructures\Symfony\Form\Type\Account;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\Space\Object\Persisted\AccountCluster;
use Traversable;

use function array_map;
use function iterator_to_array;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @extends AbstractType<AccountCluster>
 */
class AccountClusterType extends AbstractType
{
    /**
     * @param array<string, string|bool> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): self
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'name',
            TextType::class,
            [
                'required' => true,
                'label' => 'teknoo.space.form.account.account_cluster.name',
            ],
        );

        $builder->add(
            'slug',
            TextType::class,
            [
                'required' => false,
                'label' => 'teknoo.space.form.account.account_cluster.slug',
            ],
        );

        $builder->add(
            'type',
            ChoiceType::class,
            [
                'required' => true,
                'label' => 'teknoo.space.form.account.account_cluster.type',
                'choices' => [
                    'Kubernetes' => 'kubernetes',
                ],
            ],
        );

        $builder->add(
            'masterAddress',
            TextType::class,
            [
                'required' => true,
                'label' => 'teknoo.space.form.account.account_cluster.master_address',
            ],
        );

        $builder->add(
            'storageProvisioner',
            TextType::class,
            [
                'required' => true,
                'label' => 'teknoo.space.form.account.account_cluster.storage_provisioner',
            ],
        );

        $builder->add(
            'dashboardAddress',
            TextType::class,
            [
                'required' => false,
                'label' => 'teknoo.space.form.account.account_cluster.dashboard_address',
            ],
        );

        $builder->add(
            'caCertificate',
            TextareaType::class,
            [
                'required' => true,
                'label' => 'teknoo.space.form.account.account_cluster.ca_certificate',
            ],
        );

        $builder->add(
            'token',
            TextareaType::class,
            [
                'required' => false,
                'label' => 'teknoo.space.form.account.account_cluster.token',
            ],
        );

        $builder->add(
            'supportRegistry',
            CheckboxType::class,
            [
                'required' => false,
                'label' => 'teknoo.space.form.account.account_cluster.support_registry',
                'false_values' => [
                    null,
                    '0',
                    '',
                ],
            ],
        );

        $builder->add(
            'registryUrl',
            TextType::class,
            [
                'required' => false,
                'label' => 'teknoo.space.form.account.account_cluster.registry_url',
            ],
        );

        $builder->add(
            'useHnc',
            CheckboxType::class,
            [
                'required' => false,
                'label' => 'teknoo.space.form.account.account_cluster.use_hnc',
                'false_values' => [
                    null,
                    '0',
                    '',
                ],
            ],
        );

        $builder->setDataMapper(
            new class () implements DataMapperInterface {
                /**
                 * @param Traversable<string, FormInterface<string|int|bool>> $forms
                 * @param ?AccountCluster $data
                 */
                public function mapDataToForms($data, $forms): void
                {
                    if (!$data instanceof AccountCluster) {
                        return;
                    }

                    $visitors = array_map(
                        fn (FormInterface $form): callable => $form->setData(...),
                        iterator_to_array($forms)
                    );
                    $data->visit($visitors);
                }

                /**
                 * @param Traversable<string, FormInterface<AccountCluster>> $forms
                 * @param ?AccountCluster $data
                 */
                public function mapFormsToData($forms, &$data): void
                {
                    if (!$data instanceof AccountCluster) {
                        return;
                    }

                    $forms = iterator_to_array($forms);
                    $data->setName((string) $forms['name']->getData());
                    $data->setSlug((string) $forms['slug']->getData());
                    $data->setType((string) $forms['type']->getData());
                    $data->setMasterAddress((string) $forms['masterAddress']->getData());
                    $data->setStorageProvisioner((string) $forms['storageProvisioner']->getData());
                    $data->setDashboardAddress((string) $forms['dashboardAddress']->getData());
                    $data->setCaCertificate((string) $forms['caCertificate']->getData());
                    $data->setToken((string) $forms['token']->getData());
                    $data->setSupportRegistry(!empty($forms['supportRegistry']->getData()));
                    $data->setRegistryUrl((string) $forms['registryUrl']->getData());
                    $data->setUseHnc(!empty($forms['useHnc']->getData()));
                }
            }
        );

        return $this;
    }

    public function configureOptions(OptionsResolver $resolver): self
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => AccountCluster::class,
        ]);

        return $this;
    }
}
