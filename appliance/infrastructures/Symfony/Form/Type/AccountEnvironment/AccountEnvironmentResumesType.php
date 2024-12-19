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

namespace Teknoo\Space\Infrastructures\Symfony\Form\Type\AccountEnvironment;

use DomainException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\EqualTo;
use Teknoo\Space\Object\Config\Cluster;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\SubscriptionPlan;
use Teknoo\Space\Object\DTO\AccountEnvironmentResume;

use function in_array;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountEnvironmentResumesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): self
    {
        if (
            empty($options['subscriptionPlan'])
            || !$options['subscriptionPlan'] instanceof SubscriptionPlan
        ) {
            throw new DomainException("Missing subscription plan for this account");
        }

        if (
            empty($options['clusterCatalog'])
            || !$options['clusterCatalog'] instanceof ClusterCatalog
        ) {
            throw new DomainException("Missing cluster catalog for this account");
        }

        $subscriptionPlan = $options['subscriptionPlan'];
        $clustersInPlan = $subscriptionPlan->getClusters();
        $clusterCatalog = $options['clusterCatalog'];
        $clustersList = [];
        /** @var Cluster $cluster */
        foreach ($clusterCatalog as $cluster) {
            if (in_array($cluster->name, $clustersInPlan) || $cluster->isExternal) {
                $clustersList[$cluster->name] = $cluster->name;
            }
        }

        $builder->add(
            'clusterName',
            ChoiceType::class,
            [
                'required' => true,
                'choices' => $clustersList,
                'label' => 'teknoo.space.form.account_environment.cluster_name',
            ],
        );

        $builder->add(
            'envName',
            TextType::class,
            [
                'required' => true,
                'label' => 'teknoo.space.form.account_environment.env_name',
            ],
        );

        $builder->add(
            'accountEnvironmentId',
            HiddenType::class,
        );

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            static function (FormEvent $formEvent): void {
                $form = $formEvent->getForm();
                $data = $formEvent->getData();

                if (
                    !$data instanceof AccountEnvironmentResume
                    || empty($data->accountEnvironmentId)
                ) {
                    return;
                }

                foreach ($form as $children) {
                    /** @var FormInterface $children */
                    $config = $children->getConfig();
                    $options = $config->getOptions();
                    if (!isset($options['attr']) || is_array($options['attr'])) {
                        $options['attr']['readonly'] = true;
                    }

                    $options['constraints'][] = new EqualTo($children->getData());

                    $typeClass = ($config->getType()->getInnerType())::class;

                    $form->add(
                        child: $children->getName(),
                        type: $typeClass,
                        options: $options
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
            'data_class' => AccountEnvironmentResume::class,
        ]);

        $resolver->setRequired(['subscriptionPlan', 'clusterCatalog']);
        $resolver->setAllowedTypes('clusterCatalog', ClusterCatalog::class);
        $resolver->setAllowedTypes('subscriptionPlan', SubscriptionPlan::class);

        return $this;
    }
}
