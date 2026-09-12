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
use Teknoo\Space\Infrastructures\Symfony\Form\Type\Job\ApiNewJobType;
use Teknoo\Space\Object\DTO\JobVar;
use Teknoo\Space\Object\DTO\NewJob;

/**
 * Class NewJobTypeTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ApiNewJobType::class)]
class ApiNewJobTypeTest extends TestCase
{
    private ApiNewJobType $apiNewJobType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();


        $this->apiNewJobType = new ApiNewJobType();
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertIsString($this->apiNewJobType->getBlockPrefix());
    }

    public function testBuildForm(): void
    {
        $this->apiNewJobType->buildForm(
            $this->createStub(FormBuilderInterface::class),
            ['foo' => 'bar', 'environmentsList' => ['prod']],
        );
        $this->assertTrue(true);
    }

    public function testConfigureOptions(): void
    {
        $this->apiNewJobType->configureOptions(
            $this->createStub(OptionsResolver::class),
        );
        $this->assertTrue(true);
    }

    public function testBuildFormInApiModeWithPreSubmitListener(): void
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

        $this->apiNewJobType->buildForm($builder, ['environmentsList' => ['prod'], 'api' => 'json']);

        $this->assertCount(1, $listeners[FormEvents::PRE_SUBMIT]);
        $listener = $listeners[FormEvents::PRE_SUBMIT][0];

        $form = $this->createStub(FormInterface::class);
        $form->method('getNormData')->willReturn(null);
        $event = new FormEvent($form, ['variables' => []]);
        $listener($event);
        $this->assertSame(['variables' => []], $event->getData());

        $newJob = new NewJob(
            taskId: 'task',
            variables: [
                new JobVar(id: 'i', name: 'v1', value: 'x', secret: true, encryptionAlgorithm: 'rsa'),
            ],
        );
        $form = $this->createStub(FormInterface::class);
        $form->method('getNormData')->willReturn($newJob);
        $event = new FormEvent($form, ['variables' => ['v1' => ['value' => 'y']]]);
        $listener($event);

        $data = $event->getData();
        $this->assertSame('i', $data['variables'][0]['id']);
        $this->assertSame('v1', $data['variables'][0]['name']);
        $this->assertSame('y', $data['variables'][0]['value']);
        $this->assertTrue($data['variables'][0]['wasSecret']);
        $this->assertSame('rsa', $data['variables'][0]['encryptionAlgorithm']);
    }
}
