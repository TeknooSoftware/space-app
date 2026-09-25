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
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\East\Common\Contracts\User\AuthDataInterface;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\User;
use Teknoo\Space\Infrastructures\Symfony\Form\Type\User\PasswordType;

/**
 * Class PasswordTypeTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(PasswordType::class)]
class PasswordTypeTest extends TestCase
{
    private PasswordType $passwordType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();


        $this->passwordType = new PasswordType();
    }

    public function testBuildForm(): void
    {
        $this->passwordType->buildForm(
            $this->createStub(FormBuilderInterface::class),
            ['foo' => 'bar'],
        );
        $this->assertTrue(true);
    }

    public function testConfigureOptions(): void
    {
        $this->passwordType->configureOptions(
            $this->createStub(OptionsResolver::class),
        );
        $this->assertTrue(true);
    }

    public function testBuildFormInApiMode(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->never())->method('add');

        $this->passwordType->buildForm($builder, ['api' => 'json']);
    }

    public function testBuildFormPostSetDataListener(): void
    {
        $listeners = [];
        $builder = $this->createStub(FormBuilderInterface::class);
        $builder->method('add')->willReturnSelf();
        $builder->method('addEventListener')
            ->willReturnCallback(
                function (string $eventName, callable $listener) use (&$listeners, $builder): FormBuilderInterface {
                    $listeners[$eventName][] = $listener;

                    return $builder;
                }
            );

        $this->passwordType->buildForm($builder, ['api' => null]);

        $this->assertCount(1, $listeners[FormEvents::POST_SET_DATA]);
        $listener = $listeners[FormEvents::POST_SET_DATA][0];

        $storedPassword = new StoredPassword();
        $user = (new User())->setAuthData([$storedPassword]);
        $spForm = $this->createMock(FormInterface::class);
        $spForm->expects($this->once())
            ->method('setData')
            ->with($storedPassword)
            ->willReturnSelf();
        $form = $this->createStub(FormInterface::class);
        $form->method('get')->willReturn($spForm);
        $listener(new FormEvent($form, $user));
        $this->assertCount(1, $user->getAuthData());

        $otherAuthData = $this->createStub(AuthDataInterface::class);
        $user = (new User())->setAuthData([$otherAuthData]);
        $spForm = $this->createMock(FormInterface::class);
        $spForm->expects($this->once())
            ->method('setData')
            ->with($this->isInstanceOf(StoredPassword::class))
            ->willReturnSelf();
        $form = $this->createStub(FormInterface::class);
        $form->method('get')->willReturn($spForm);
        $listener(new FormEvent($form, $user));
        $this->assertCount(2, $user->getAuthData());

        $user = new User();
        $spForm = $this->createMock(FormInterface::class);
        $spForm->expects($this->once())
            ->method('setData')
            ->with($this->isInstanceOf(StoredPassword::class))
            ->willReturnSelf();
        $form = $this->createStub(FormInterface::class);
        $form->method('get')->willReturn($spForm);
        $listener(new FormEvent($form, $user));
        $this->assertCount(1, $user->getAuthData());
    }
}
