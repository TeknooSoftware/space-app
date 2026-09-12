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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Form\Type\User;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContext;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Space\Infrastructures\Symfony\Form\Type\User\ApiKeysAuthType;
use Teknoo\Space\Object\Persisted\ApiKeysAuth;
use Teknoo\Space\Object\Persisted\ApiKeyToken;

/**
 * Class ApiKeysAuthTypeTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ApiKeysAuthType::class)]
class ApiKeysAuthTypeTest extends TestCase
{
    private function buildTokenStorage(?TokenInterface $token): TokenStorageInterface
    {
        $tokenStorage = $this->createStub(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        return $tokenStorage;
    }

    public function testBuildFormWithoutToken(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->never())->method('add');

        $type = new ApiKeysAuthType(
            $this->createStub(DatesService::class),
            $this->buildTokenStorage(null),
            $this->createStub(UserPasswordHasherInterface::class),
        );

        $type->buildForm($builder, []);
    }

    public function testBuildFormWithNotAnAbstractUser(): void
    {
        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($this->createStub(UserInterface::class));

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->never())->method('add');

        $type = new ApiKeysAuthType(
            $this->createStub(DatesService::class),
            $this->buildTokenStorage($token),
            $this->createStub(UserPasswordHasherInterface::class),
        );

        $type->buildForm($builder, []);
    }

    public function testBuildForm(): void
    {
        $user = new User();

        $symfonyUser = $this->createStub(AbstractUser::class);
        $symfonyUser->method('getWrappedUser')->willReturn($user);

        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($symfonyUser);

        $date = new DateTimeImmutable('2024-01-01');
        $datesService = $this->createMock(DatesService::class);
        $datesService->expects($this->exactly(2))
            ->method('passMeTheDate')
            ->with($this->isCallable())
            ->willReturnCallback(
                static function (callable $setter) use ($date, $datesService): DatesService {
                    $setter($date);

                    return $datesService;
                }
            );

        $hasher = $this->createStub(UserPasswordHasherInterface::class);
        $hasher->method('hashPassword')->willReturn('hashed');

        $type = new ApiKeysAuthType(
            $datesService,
            $this->buildTokenStorage($token),
            $hasher,
        );

        $added = [];
        $listeners = [];
        $builder = $this->createStub(FormBuilderInterface::class);
        $builder->method('add')
            ->willReturnCallback(
                function (string $child, ?string $type = null, array $options = []) use (&$added, $builder) {
                    $added[$child] = $options;

                    return $builder;
                }
            );
        $builder->method('addEventListener')
            ->willReturnCallback(
                function (string $event, callable $listener) use (&$listeners, $builder) {
                    $listeners[$event][] = $listener;

                    return $builder;
                }
            );

        $type->buildForm($builder, []);

        $this->assertArrayHasKey('name', $added);
        $this->assertArrayHasKey('expiresAt', $added);

        $callback = null;
        foreach ($added['name']['constraints'] as $constraint) {
            if ($constraint instanceof Callback) {
                $callback = $constraint->callback;
            }
        }
        $this->assertIsCallable($callback);

        $context = $this->createMock(ExecutionContext::class);
        $context->expects($this->never())->method('addViolation');
        $callback('name', $context, $user);

        $user->addAuthData(new ApiKeysAuth([new ApiKeyToken('name')]));
        $context = $this->createMock(ExecutionContext::class);
        $context->expects($this->once())->method('addViolation');
        $callback('name', $context, $user);

        $this->assertCount(1, $listeners[FormEvents::POST_SUBMIT]);
        $listener = $listeners[FormEvents::POST_SUBMIT][0];

        $invalidForm = $this->createStub(FormInterface::class);
        $invalidForm->method('isValid')->willReturn(false);
        $listener(new FormEvent($invalidForm, new ApiKeyToken('other')));

        $validForm = $this->createStub(FormInterface::class);
        $validForm->method('isValid')->willReturn(true);
        $listener(new FormEvent($validForm, 'not a token'));

        $apiKeyToken = new ApiKeyToken('other');
        $listener(new FormEvent($validForm, $apiKeyToken));
        $this->assertSame('hashed', $apiKeyToken->getTokenHash());
        $this->assertStringStartsWith('sp_', $apiKeyToken->getToken());
        $this->assertSame($date, $apiKeyToken->getCreatedAt());
        $this->assertInstanceOf(ApiKeysAuth::class, $user->getOneAuthData(ApiKeysAuth::class));

        $apiKeyToken = new ApiKeyToken('third');
        $listener(new FormEvent($validForm, $apiKeyToken));
        $this->assertSame($apiKeyToken, $user->getOneAuthData(ApiKeysAuth::class)?->getToken('third'));
    }

    public function testConfigureOptions(): void
    {
        $type = new ApiKeysAuthType(
            $this->createStub(DatesService::class),
            $this->buildTokenStorage(null),
            $this->createStub(UserPasswordHasherInterface::class),
        );

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);

        $this->assertSame(ApiKeyToken::class, $resolver->resolve([])['data_class']);
    }
}
