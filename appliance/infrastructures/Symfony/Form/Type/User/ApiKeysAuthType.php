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

namespace Teknoo\Space\Infrastructures\Symfony\Form\Type\User;

use DateTimeInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContext;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Space\Infrastructures\Symfony\Object\ApiKeysAuthUser;
use Teknoo\Space\Object\Persisted\ApiKeysAuth;
use Teknoo\Space\Object\Persisted\ApiKeyToken;

use function bin2hex;
use function random_bytes;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @extends AbstractType<ApiKeyToken>
 */
class ApiKeysAuthType extends AbstractType
{
    public function __construct(
        private readonly DatesService $datesService,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): self
    {
        $sfToken = $this->tokenStorage->getToken();
        if (!$sfToken instanceof TokenInterface) {
            return $this;
        }

        $symfonyUser = $sfToken->getUser();
        if (!$symfonyUser instanceof AbstractUser) {
            return $this;
        }

        $user = $symfonyUser->getWrappedUser();

        $builder->add(
            'name',
            TextType::class,
            [
                'required' => true,
                'label' => 'teknoo.space.form.user.api_key.name',
                'attr' => [
                    'pattern' => '/^[a-z][a-z0-9\_\-]{3,}$/',
                ],
                'constraints' => [
                    new Regex(
                        [
                            'pattern' => '/^[a-z][a-z0-9\_\-]{3,}$/',
                            'message' => 'teknoo.space.form.user.api_key.name.regex_error',
                        ]
                    ),
                    new Callback(
                        callback: function (string $name, ExecutionContext $context, User $payload) {
                            /** @var ?ApiKeysAuth $apiKeys */
                            $apiKeys = $payload->getOneAuthData(ApiKeysAuth::class);
                            if ($apiKeys?->getToken($name)) {
                                $context->addViolation('teknoo.space.error.space_user.api_key_already_exists');
                            }
                        },
                        payload: $user,
                    )
                ],
            ],
        );

        $builder->add(
            'expiresAt',
            DateType::class,
            [
                'required' => true,
                'html5' => true,
                'widget' => 'single_text',
                'label' => 'teknoo.space.form.user.api_key.expiration',
            ],
        );

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($user): void {
                if (!$event->getForm()->isValid()) {
                    return;
                }

                $data = $event->getData();
                if (!$data instanceof ApiKeyToken) {
                    return;
                }

                $this->datesService->passMeTheDate(
                    function (DateTimeInterface $now) use ($user, $data): void {
                        $token = 'sp_' . bin2hex(random_bytes(32));

                        $data->setToken($token);
                        $data->setCreatedAt($now);
                        $data->setExpired(false);

                        $data->setTokenHash(
                            $this->passwordHasher->hashPassword(
                                new ApiKeysAuthUser(
                                    $user,
                                    $data,
                                ),
                                $token
                            )
                        );

                        /** @var ApiKeysAuth $apiKeys */
                        $apiKeys = $user->getOneAuthData(ApiKeysAuth::class) ?? new ApiKeysAuth();
                        $apiKeys->addToken($data);
                        $user->addAuthData($apiKeys);
                    }
                );
            }
        );


        return $this;
    }

    public function configureOptions(OptionsResolver $resolver): self
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => ApiKeyToken::class,
        ]);

        return $this;
    }
}
