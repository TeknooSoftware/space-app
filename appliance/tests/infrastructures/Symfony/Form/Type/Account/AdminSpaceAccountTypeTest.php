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
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\Space\Infrastructures\Symfony\Form\Type\Account\AdminSpaceAccountType;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\SubscriptionPlan;

/**
 * Class AdminSpaceAccountTypeTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AdminSpaceAccountType::class)]
class AdminSpaceAccountTypeTest extends TestCase
{
    private AdminSpaceAccountType $adminSpaceAccountType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();


        $this->adminSpaceAccountType = new AdminSpaceAccountType();
    }

    public function testBuildForm(): void
    {
        $this->adminSpaceAccountType->buildForm(
            $this->createStub(FormBuilderInterface::class),
            ['foo' => 'bar', 'doctrine_type' => 'odm', 'namespaceIsReadonly' => true,],
        );
        $this->assertTrue(true);
    }

    public function testConfigureOptions(): void
    {
        $this->adminSpaceAccountType->configureOptions(
            $this->createStub(OptionsResolver::class),
        );
        $this->assertTrue(true);
    }

    public function testBuildFormWithEnvManagementAndPreSubmitListener(): void
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

        $options = [
            'api' => null,
            'enableEnvManagement' => true,
            'subscriptionPlan' => new SubscriptionPlan('id', 'name', [], envsCountAllowed: 3),
            'clusterCatalog' => new ClusterCatalog([], []),
            'doctrine_type' => 'mongodb',
            'namespaceIsReadonly' => false,
        ];
        $this->adminSpaceAccountType->buildForm($builder, $options);

        $options['subscriptionPlan'] = new SubscriptionPlan('id', 'name', [], envsCountAllowed: 0);
        $this->adminSpaceAccountType->buildForm($builder, $options);

        $this->assertCount(2, $listeners[FormEvents::PRE_SUBMIT]);
        $listener = $listeners[FormEvents::PRE_SUBMIT][0];

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('remove')
            ->with('environments')
            ->willReturnSelf();

        $event = new FormEvent($form, 'notArray');
        $listener($event);
        $this->assertSame('notArray', $event->getData());

        $event = new FormEvent($form, ['environments' => ['x']]);
        $listener($event);
        $this->assertSame(['environments' => ['x']], $event->getData());

        $event = new FormEvent($form, []);
        $listener($event);
        $this->assertSame([], $event->getData());

        $event = new FormEvent($form, ['environments' => 'notArray']);
        $listener($event);
        $this->assertSame(['environments' => []], $event->getData());
    }
}
