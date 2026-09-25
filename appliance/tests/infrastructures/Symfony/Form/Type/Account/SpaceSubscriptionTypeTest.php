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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Form\Type\Account;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Infrastructures\Symfony\Form\Type\Account\SpaceSubscriptionType;
use Teknoo\Space\Infrastructures\Symfony\Service\Account\CodeGenerator;

/**
 * Class SpaceSubscriptionTypeTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SpaceSubscriptionType::class)]
class SpaceSubscriptionTypeTest extends TestCase
{
    private SpaceSubscriptionType $spaceSubscriptionType;

    private CodeGenerator&Stub $codeGenerator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->codeGenerator = $this->createStub(CodeGenerator::class);
        $this->spaceSubscriptionType = new SpaceSubscriptionType($this->codeGenerator);
    }

    public function testBuildForm(): void
    {
        $this->spaceSubscriptionType->buildForm(
            $this->createStub(FormBuilderInterface::class),
            ['foo' => 'bar'],
        );
        $this->assertTrue(true);
    }

    public function testConfigureOptions(): void
    {
        $this->spaceSubscriptionType->configureOptions(
            $this->createStub(OptionsResolver::class),
        );
        $this->assertTrue(true);
    }

    public function testBuildFormWithoutCodeRestriction(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->exactly(2))
            ->method('add')
            ->willReturnSelf();
        $builder->expects($this->never())
            ->method('addEventListener');

        $this->assertInstanceOf(
            SpaceSubscriptionType::class,
            $this->spaceSubscriptionType->setEnableCodeRestriction(false),
        );
        $this->spaceSubscriptionType->buildForm($builder, ['doctrine_type' => 'odm']);
    }

    /**
     * @return array<string, array<int, callable>>
     */
    private function buildFormAndCaptureListeners(): array
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

        $this->spaceSubscriptionType->buildForm($builder, ['doctrine_type' => 'odm']);

        return $listeners;
    }

    public function testBuildFormPreSubmitListenerWithValidCode(): void
    {
        $this->codeGenerator->method('verify')
            ->willReturnCallback(
                function (string $value, string $code, PromiseInterface $promise): CodeGenerator {
                    $this->assertSame('Foo', $value);
                    $this->assertSame('abc', $code);
                    $promise->success($code);

                    return $this->codeGenerator;
                }
            );

        $listeners = $this->buildFormAndCaptureListeners();
        $this->assertCount(1, $listeners[FormEvents::PRE_SUBMIT]);
        $listener = $listeners[FormEvents::PRE_SUBMIT][0];

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->never())->method('addError');

        $listener(
            new FormEvent(
                $form,
                ['code' => ' abc ', 'account' => ['account' => ['name' => ' Foo ']]],
            ),
        );
    }

    public function testBuildFormPreSubmitListenerWithInvalidCode(): void
    {
        $this->codeGenerator->method('verify')
            ->willReturnCallback(
                function (string $value, string $code, PromiseInterface $promise): CodeGenerator {
                    $this->assertSame('', $value);
                    $this->assertSame('', $code);
                    $promise->fail(new RuntimeException('invalid code'));

                    return $this->codeGenerator;
                }
            );

        $listeners = $this->buildFormAndCaptureListeners();
        $listener = $listeners[FormEvents::PRE_SUBMIT][0];

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('addError')
            ->with($this->callback(fn (FormError $error): bool => 'invalid code' === $error->getMessage()))
            ->willReturnSelf();

        $listener(new FormEvent($form, []));
    }
}
