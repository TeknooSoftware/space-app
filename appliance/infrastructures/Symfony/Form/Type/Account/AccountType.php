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

namespace Teknoo\Space\Infrastructures\Symfony\Form\Type\Account;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\East\Paas\Object\Account;
use Traversable;

use function array_map;
use function iterator_to_array;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountType extends AbstractType
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
                'label' => 'teknoo.space.form.account.account.name',
            ],
        );

        $builder->setDataMapper(new class () implements DataMapperInterface {
            /**
             * @param Traversable<string, FormInterface> $forms
             * @param ?Account $data
             */
            public function mapDataToForms($data, $forms): void
            {
                if (!$data instanceof Account) {
                    return;
                }

                $visitors = array_map(
                    fn (FormInterface $form): callable => $form->setData(...),
                    iterator_to_array($forms)
                );
                $data->visit($visitors);
            }

            /**
             * @param Traversable<string, FormInterface<AccountType>> $forms
             * @param ?Account $data
             */
            public function mapFormsToData($forms, &$data): void
            {
                if (!$data instanceof Account) {
                    return;
                }

                $forms = iterator_to_array($forms);
                $data->setName((string) $forms['name']->getData());
            }
        });

        return $this;
    }

    public function configureOptions(OptionsResolver $resolver): self
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Account::class,
        ]);

        return $this;
    }
}
