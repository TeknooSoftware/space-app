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
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Teknoo\East\CommonBundle\Contracts\Form\FormApiAwareInterface;
use Teknoo\Space\Infrastructures\Symfony\Form\Type\AccountData\AccountDataType;
use Teknoo\Space\Infrastructures\Symfony\Form\Type\AccountEnvironment\AccountEnvironmentResumesType;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\SubscriptionPlan;
use Teknoo\Space\Object\DTO\SpaceAccount;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SpaceAccountType extends AbstractType implements FormApiAwareInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): self
    {
        if (empty($options['api']) || empty($options['enableEnvManagement'])) {
            $builder->add(
                'account',
                AccountType::class,
            );

            $builder->add(
                'accountData',
                AccountDataType::class,
                [
                    'canUpdateSubscription' => false,
                ]
            );
        }

        if (
            !empty($options['enableEnvManagement'])
            && !empty($options['subscriptionPlan']) && $options['subscriptionPlan'] instanceof SubscriptionPlan
            && !empty($options['clusterCatalog']) && $options['clusterCatalog'] instanceof ClusterCatalog
        ) {
            $contraints = [];
            if (0 < $options['subscriptionPlan']->envsCountAllowed) {
                $contraints[] = new Count([
                    'max' => $options['subscriptionPlan']->envsCountAllowed,
                    'maxMessage' => "teknoo.space.error.space_account.environments.exceeded.{{ limit }}",
                ]);
            }

            $builder->add(
                'environments',
                CollectionType::class,
                [
                    'entry_type' => AccountEnvironmentResumesType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                    'entry_options' => [
                        'subscriptionPlan' => $options['subscriptionPlan'],
                        'clusterCatalog' => $options['clusterCatalog'],
                    ],
                    'constraints' => $contraints,
                ],
            );
        }

        return $this;
    }

    public function configureOptions(OptionsResolver $resolver): self
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => SpaceAccount::class,
            'api' => null,
            'enableEnvManagement' => null,
            'subscriptionPlan' => null,
            'clusterCatalog' => null,
        ]);

        $resolver->setRequired(['doctrine_type']);
        $resolver->setAllowedTypes('doctrine_type', 'string');
        $resolver->setAllowedTypes('clusterCatalog', [ClusterCatalog::class, 'null']);
        $resolver->setAllowedTypes('subscriptionPlan', [SubscriptionPlan::class, 'null']);
        $resolver->setAllowedTypes('enableEnvManagement', ['null', 'bool']);

        return $this;
    }
}
