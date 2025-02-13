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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Infrastructures\Symfony\Service\Account\CodeGenerator;
use Throwable;

use function is_string;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CodeGeneratorType extends AbstractType
{
    public function __construct(
        private CodeGenerator $codeGenerator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): self
    {
        $builder->add(
            'company',
            TextType::class,
            [
                'required' => true,
                'mapped' => false,
                'label' => 'teknoo.space.form.account.code_generator.company',
            ]
        );

        $builder->add(
            'code',
            TextType::class,
            [
                'required' => false,
                'mapped' => false,
                'label' => 'teknoo.space.form.account.code_generator.subscription_code',
                'attr' => [
                    'readonly' => true,
                ]
            ]
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                /** @var array{code: string, company: string} $data */
                $data = $event->getData();

                if (empty($data['company'])) {
                    return;
                }

                /** @var Promise<string, string|Throwable, mixed> $promise */
                $promise = new Promise(
                    fn (string $code) => $code,
                    fn (Throwable $error) => $error,
                );

                $this->codeGenerator->generateCode(
                    $data['company'],
                    $promise
                );

                if (is_string($code = $promise->fetchResult())) {
                    $data['code'] = $code;
                    $event->setData($data);
                }
            }
        );


        return $this;
    }
}
