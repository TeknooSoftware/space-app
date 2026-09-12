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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Form\Type\Job;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\Space\Infrastructures\Symfony\Form\Type\Job\JobVarType;
use Teknoo\Space\Object\DTO\JobVar;

/**
 * Class JobVarTypeTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(JobVarType::class)]
class JobVarTypeTest extends TestCase
{
    private JobVarType $jobVarType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();


        $this->jobVarType = new JobVarType();
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertIsString($this->jobVarType->getBlockPrefix());
    }

    public function testBuildForm(): void
    {
        $this->jobVarType->buildForm(
            $this->createStub(FormBuilderInterface::class),
            ['foo' => 'bar'],
        );
        $this->assertTrue(true);
    }

    public function testConfigureOptions(): void
    {
        $this->jobVarType->configureOptions(
            $this->createStub(OptionsResolver::class),
        );
        $this->assertTrue(true);
    }

    /**
     * @return array<string, array<int, callable>>
     */
    private function buildFormWithPasswordForSecret(): array
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

        $this->jobVarType->buildForm($builder, ['usePasswordForSecret' => true]);

        return $listeners;
    }

    public function testBuildFormWithPasswordForSecretPostSetData(): void
    {
        $listeners = $this->buildFormWithPasswordForSecret();
        $this->assertCount(1, $listeners[FormEvents::POST_SET_DATA]);
        $listener = $listeners[FormEvents::POST_SET_DATA][0];

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('add')
            ->with('value')
            ->willReturnSelf();

        $listener(new FormEvent($form, new JobVar(name: 'a', secret: true)));
        $listener(new FormEvent($form, null));
    }

    public function testBuildFormWithPasswordForSecretPreSubmitWithoutJobVar(): void
    {
        $listeners = $this->buildFormWithPasswordForSecret();
        $this->assertCount(1, $listeners[FormEvents::PRE_SUBMIT]);
        $listener = $listeners[FormEvents::PRE_SUBMIT][0];

        $form = $this->createStub(FormInterface::class);
        $form->method('getNormData')->willReturn(null);

        $event = new FormEvent($form, ['value' => 'foo']);
        $listener($event);
        $this->assertSame(['value' => 'foo', 'canPersist' => true], $event->getData());

        $event = new FormEvent($form, 'foo');
        $listener($event);
        $this->assertSame('foo', $event->getData());
    }

    public function testBuildFormWithPasswordForSecretPreSubmitWithJobVar(): void
    {
        $listeners = $this->buildFormWithPasswordForSecret();
        $listener = $listeners[FormEvents::PRE_SUBMIT][0];

        $secretVar = new JobVar(
            id: 'i',
            name: 'n',
            value: 'v',
            secret: true,
            wasSecret: true,
            encryptionAlgorithm: 'rsa',
        );
        $form = $this->createStub(FormInterface::class);
        $form->method('getNormData')->willReturn($secretVar);

        $event = new FormEvent($form, ['value' => '']);
        $listener($event);
        $data = $event->getData();
        $this->assertSame('i', $data['id']);
        $this->assertTrue($data['wasSecret']);
        $this->assertTrue($data['secret']);
        $this->assertSame('rsa', $data['encryptionAlgorithm']);
        $this->assertSame('v', $data['value']);

        $clearVar = new JobVar(
            id: 'i',
            name: 'n',
            value: 'v',
            secret: false,
        );
        $form = $this->createStub(FormInterface::class);
        $form->method('getNormData')->willReturn($clearVar);

        $event = new FormEvent($form, ['value' => 'other']);
        $listener($event);
        $data = $event->getData();
        $this->assertSame('other', $data['value']);
        $this->assertNull($data['encryptionAlgorithm']);
        $this->assertTrue($data['canPersist']);

        $event = new FormEvent($form, ['value' => 'v']);
        $listener($event);
        $data = $event->getData();
        $this->assertSame('v', $data['value']);
        $this->assertFalse($data['canPersist']);
    }
}
