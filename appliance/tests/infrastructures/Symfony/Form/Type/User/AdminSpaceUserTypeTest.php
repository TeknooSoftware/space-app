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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\East\CommonBundle\Form\Type\ApiUserType;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Query\QueryElementInterface;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Infrastructures\Doctrine\Form\UserData\UserDataType;
use Teknoo\Space\Infrastructures\Symfony\Form\Type\User\AdminSpaceUserType;
use Teknoo\Space\Loader\Meta\SpaceAccountLoader;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\DTO\SpaceUser;
use Teknoo\Space\Query\Account\FetchAccountFromUser;

/**
 * Class AdminSpaceUserTypeTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AdminSpaceUserType::class)]
class AdminSpaceUserTypeTest extends TestCase
{
    private SpaceAccountLoader&MockObject $spaceAccountLoader;

    private AdminSpaceUserType $adminSpaceUserType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->spaceAccountLoader = $this->createMock(SpaceAccountLoader::class);

        $this->adminSpaceUserType = new AdminSpaceUserType($this->spaceAccountLoader, 'ROLE_ADMIN', 'ROLE_PENDING');
    }

    /**
     * @return array{0: array<string, string>, 1: array<string, callable[]>, 2: array<string, mixed>}
     */
    private function buildAndListen(): array
    {
        $children = [];
        $listeners = [];
        $rolesChild = [];
        $userBuilder = $this->createStub(FormBuilderInterface::class);
        $userBuilder->method('add')
            ->willReturnCallback(
                function (string $child, ?string $type = null, array $options = []) use (&$rolesChild, $userBuilder) {
                    $rolesChild = ['child' => $child, 'type' => $type, 'options' => $options];

                    return $userBuilder;
                }
            );

        $builder = $this->createStub(FormBuilderInterface::class);
        $builder->method('get')->willReturn($userBuilder);
        $builder->method('add')
            ->willReturnCallback(
                function (string $child, ?string $type = null) use (&$children, $builder): FormBuilderInterface {
                    $children[$child] = (string) $type;

                    return $builder;
                }
            );
        $builder->method('addEventListener')
            ->willReturnCallback(
                function (string $eventName, callable $listener) use (&$listeners, $builder): FormBuilderInterface {
                    $listeners[$eventName][] = $listener;

                    return $builder;
                }
            );

        $this->adminSpaceUserType->buildForm($builder, ['api' => null]);

        return [$children, $listeners, $rolesChild];
    }

    public function testBuildFormHasNoPasswordOnTheWebNorOnTheApi(): void
    {
        $this->spaceAccountLoader->expects($this->never())->method('fetch');

        [$children] = $this->buildAndListen();

        $this->assertSame(
            [
                'user' => ApiUserType::class,
                'userData' => UserDataType::class,
            ],
            $children,
        );
    }

    public function testBuildFormAddsThePendingRoleToTheRolesOfTheUser(): void
    {
        $this->spaceAccountLoader->expects($this->never())->method('fetch');

        [, , $rolesChild] = $this->buildAndListen();

        $this->assertSame(
            [
                'child' => 'roles',
                'type' => ChoiceType::class,
                'options' => [
                    'required' => true,
                    'multiple' => true,
                    'choices' => [
                        'user' => 'ROLE_USER',
                        'admin' => 'ROLE_ADMIN',
                        'pending' => 'ROLE_PENDING',
                    ],
                ],
            ],
            $rolesChild,
        );
    }

    private function submit(mixed $data, ?FormInterface $roles = null): void
    {
        [, $listeners] = $this->buildAndListen();
        $this->assertCount(1, $listeners[FormEvents::POST_SUBMIT] ?? []);

        $roles ??= $this->createStub(FormInterface::class);
        $userForm = $this->createStub(FormInterface::class);
        $userForm->method('get')->willReturn($roles);
        $form = $this->createStub(FormInterface::class);
        $form->method('get')->willReturn($userForm);

        $listeners[FormEvents::POST_SUBMIT][0](new FormEvent($form, $data));
    }

    /**
     * @param string[] $roles
     */
    private function createUser(?string $id, array $roles): SpaceUser
    {
        $user = new User();
        if (null !== $id) {
            $user->setId($id);
        }
        $user->setRoles($roles);

        return new SpaceUser($user);
    }

    public function testTheGuardIgnoresAnOtherData(): void
    {
        $this->spaceAccountLoader->expects($this->never())->method('fetch');

        $this->submit(new stdClass());
    }

    public function testTheGuardIgnoresANewUser(): void
    {
        $this->spaceAccountLoader->expects($this->never())->method('fetch');

        $this->submit($this->createUser(null, ['ROLE_USER', 'ROLE_ADMIN']));
    }

    public function testTheGuardIgnoresAUserWithoutTheAdminRole(): void
    {
        $this->spaceAccountLoader->expects($this->never())->method('fetch');

        $this->submit($this->createUser('user-1', ['ROLE_USER']));
    }

    public function testTheGuardRefusesTheAdminRoleToAUserOfAnAccount(): void
    {
        $this->spaceAccountLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function (QueryElementInterface $query, PromiseInterface $promise): LoaderInterface {
                    $this->assertInstanceOf(FetchAccountFromUser::class, $query);
                    $promise->success(new SpaceAccount());

                    return $this->spaceAccountLoader;
                }
            );

        $roles = $this->createMock(FormInterface::class);
        $roles->expects($this->once())->method('addError');

        $this->submit($this->createUser('user-1', ['ROLE_USER', 'ROLE_ADMIN']), $roles);
    }

    public function testTheGuardAcceptsTheAdminRoleForAUserWithoutAccount(): void
    {
        $this->spaceAccountLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function (QueryElementInterface $query, PromiseInterface $promise): LoaderInterface {
                    $promise->fail(new \DomainException('not found'));

                    return $this->spaceAccountLoader;
                }
            );

        $roles = $this->createMock(FormInterface::class);
        $roles->expects($this->never())->method('addError');

        $this->submit($this->createUser('user-1', ['ROLE_USER', 'ROLE_ADMIN']), $roles);
    }

    public function testTheGuardRefusesThePendingRoleWithAnOtherRole(): void
    {
        $this->spaceAccountLoader->expects($this->never())->method('fetch');

        $roles = $this->createMock(FormInterface::class);
        $roles->expects($this->once())
            ->method('addError')
            ->with($this->callback(
                fn (FormError $error): bool => 'teknoo.space.error.space_user.pending_alone' === $error->getMessage(),
            ));

        $this->submit($this->createUser(null, ['ROLE_PENDING', 'ROLE_USER']), $roles);
    }

    public function testTheGuardAcceptsThePendingRoleForANewUser(): void
    {
        $this->spaceAccountLoader->expects($this->never())->method('fetch');

        $roles = $this->createMock(FormInterface::class);
        $roles->expects($this->never())->method('addError');

        $this->submit($this->createUser(null, ['ROLE_PENDING']), $roles);
    }

    public function testTheGuardRefusesThePendingRoleToAUserOfAnAccount(): void
    {
        $this->spaceAccountLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function (QueryElementInterface $query, PromiseInterface $promise): LoaderInterface {
                    $this->assertInstanceOf(FetchAccountFromUser::class, $query);
                    $promise->success(new SpaceAccount());

                    return $this->spaceAccountLoader;
                }
            );

        $roles = $this->createMock(FormInterface::class);
        $roles->expects($this->once())
            ->method('addError')
            ->with($this->callback(
                fn (FormError $error): bool => 'teknoo.space.error.space_user.pending_in_account'
                    === $error->getMessage(),
            ));

        $this->submit($this->createUser('user-1', ['ROLE_PENDING']), $roles);
    }

    public function testTheGuardAcceptsThePendingRoleForAUserWithoutAccount(): void
    {
        $this->spaceAccountLoader->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function (QueryElementInterface $query, PromiseInterface $promise): LoaderInterface {
                    $promise->fail(new \DomainException('not found'));

                    return $this->spaceAccountLoader;
                }
            );

        $roles = $this->createMock(FormInterface::class);
        $roles->expects($this->never())->method('addError');

        $this->submit($this->createUser('user-1', ['ROLE_PENDING']), $roles);
    }

    public function testConfigureOptions(): void
    {
        $this->spaceAccountLoader->expects($this->never())->method('fetch');

        $this->adminSpaceUserType->configureOptions(
            $this->createStub(OptionsResolver::class),
        );
        $this->assertTrue(true);
    }
}
