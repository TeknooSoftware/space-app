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

namespace Teknoo\Space\Infrastructures\Symfony\Form\Type\AccountData;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\Space\Object\Config\SubscriptionPlan;
use Teknoo\Space\Object\Config\SubscriptionPlanCatalog;
use Teknoo\Space\Object\Persisted\AccountData;
use Traversable;

use function array_map;
use function iterator_to_array;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountDataType extends AbstractType
{
    public function __construct(
        private SubscriptionPlanCatalog $planCatalog,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): self
    {
        $builder->add(
            'legalName',
            TextType::class,
            [
                'required' => true,
                'label' => 'teknoo.space.form.account_data.data.legal_name',
            ],
        );

        $builder->add(
            'streetAddress',
            TextType::class,
            [
                'required' => true,
                'label' => 'teknoo.space.form.account_data.data.street_address',
            ],
        );

        $builder->add(
            'zipCode',
            TextType::class,
            [
                'required' => true,
                'label' => 'teknoo.space.form.account_data.data.zip_code',
            ],
        );

        $builder->add(
            'cityName',
            TextType::class,
            [
                'required' => true,
                'label' => 'teknoo.space.form.account_data.data.city_name',
            ],
        );

        $builder->add(
            'countryName',
            TextType::class,
            [
                'required' => true,
                'label' => 'teknoo.space.form.account_data.data.country_name',
            ],
        );

        $builder->add(
            'vatNumber',
            TextType::class,
            [
                'required' => false,
                'label' => 'teknoo.space.form.account_data.data.vat_number',
            ],
        );

        $catalog = [];
        foreach ($this->planCatalog as $plan) {
            $catalog[$plan->name] = $plan->id;
        }

        $canUpdateSubscription = $options['can_update_subscription'] ?? false;
        $builder->add(
            'subscriptionPlan',
            ChoiceType::class,
            [
                'required' => false,
                'label' => 'teknoo.space.form.account_data.data.subscription_plan',
                'disabled' => !$canUpdateSubscription,
                'choices' => $catalog,
            ],
        );

        $builder->setDataMapper(new class ($canUpdateSubscription) implements DataMapperInterface {
            public function __construct(
                private bool $canUpdateSubscription,
            ) {
            }

            /**
             * @param Traversable<string, FormInterface> $forms
             * @param ?\Teknoo\Space\Object\Persisted\AccountData $data
             */
            public function mapDataToForms($data, $forms): void
            {
                if (!$data instanceof AccountData) {
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
             * @param ?AccountData $data
             */
            public function mapFormsToData($forms, &$data): void
            {
                if (!$data instanceof AccountData) {
                    return;
                }

                $forms = iterator_to_array($forms);
                $data->setLegalName($forms['legalName']->getData() ?? '');
                $data->setStreetAddress($forms['streetAddress']->getData() ?? '');
                $data->setZipCode($forms['zipCode']->getData() ?? '');
                $data->setCityName($forms['cityName']->getData() ?? '');
                $data->setCountryName($forms['countryName']->getData() ?? '');
                $data->setVatNumber($forms['vatNumber']->getData() ?? '');

                if ($this->canUpdateSubscription) {
                    $data->setSubscriptionPlan($forms['subscriptionPlan']->getData() ?? '');
                }
            }
        });

        return $this;
    }

    public function configureOptions(OptionsResolver $resolver): self
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => AccountData::class,
            'can_update_subscription' => false,
        ]);

        return $this;
    }
}
