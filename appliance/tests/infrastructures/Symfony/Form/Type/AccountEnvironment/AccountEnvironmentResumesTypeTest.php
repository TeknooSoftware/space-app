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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Form\Type\AccountEnvironment;

use ArrayIterator;
use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\Space\Infrastructures\Symfony\Form\Type\AccountEnvironment\AccountEnvironmentResumesType;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\KubernetesCluster;
use Teknoo\Space\Object\Config\SubscriptionPlan;
use Teknoo\Space\Object\DTO\AccountEnvironmentResume;

/**
 * Class AccountEnvironmentResumesTypeTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountEnvironmentResumesType::class)]
class AccountEnvironmentResumesTypeTest extends TestCase
{
    private AccountEnvironmentResumesType $accountEnvironmentResumesType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->accountEnvironmentResumesType = new AccountEnvironmentResumesType();
    }

    public function testBuildForm(): void
    {
        $this->accountEnvironmentResumesType->buildForm(
            $this->createStub(FormBuilderInterface::class),
            [
                'subscriptionPlan' => $this->createStub(SubscriptionPlan::class),
                'clusterCatalog' => $this->createStub(ClusterCatalog::class),
            ],
        );
        $this->assertTrue(true);
    }

    public function testBuildFormWithoutSubscriptionPlan(): void
    {
        $this->expectException(DomainException::class);
        $this->accountEnvironmentResumesType->buildForm(
            $this->createStub(FormBuilderInterface::class),
            [],
        );
    }

    public function testBuildFormWithoutClusterCatalog(): void
    {
        $this->expectException(DomainException::class);
        $this->accountEnvironmentResumesType->buildForm(
            $this->createStub(FormBuilderInterface::class),
            [
                'subscriptionPlan' => new SubscriptionPlan('plan', 'Plan', []),
            ],
        );
    }

    private function buildCluster(string $name, bool $isExternal): KubernetesCluster
    {
        return new KubernetesCluster(
            name: $name,
            sluggyName: $name,
            type: 'kubernetes',
            masterAddress: 'https://master',
            storageProvisioner: 'provisioner',
            dashboardAddress: 'https://dashboard',
            kubernetesClient: static fn () => throw new DomainException('not needed'),
            token: 'token',
            supportRegistry: false,
            useHnc: false,
            isExternal: $isExternal,
        );
    }

    private function buildChild(string $name, mixed $attr): FormInterface&Stub
    {
        $type = $this->createStub(ResolvedFormTypeInterface::class);
        $type->method('getInnerType')->willReturn(new TextType());

        $config = $this->createStub(FormConfigInterface::class);
        $config->method('getOptions')->willReturn(['attr' => $attr]);
        $config->method('getType')->willReturn($type);

        $child = $this->createStub(FormInterface::class);
        $child->method('getConfig')->willReturn($config);
        $child->method('getData')->willReturn('value');
        $child->method('getName')->willReturn($name);

        return $child;
    }

    public function testBuildFormWithRealCatalogAndPostSetDataListener(): void
    {
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

        $this->accountEnvironmentResumesType->buildForm(
            $builder,
            [
                'subscriptionPlan' => new SubscriptionPlan('plan', 'Plan', [], clusters: ['c1']),
                'clusterCatalog' => new ClusterCatalog(
                    [
                        'c1' => $this->buildCluster('c1', false),
                        'c2' => $this->buildCluster('c2', true),
                        'c3' => $this->buildCluster('c3', false),
                    ],
                    [],
                ),
            ],
        );

        $this->assertSame(['c1' => 'c1', 'c2' => 'c2'], $added['clusterName']['choices']);
        $this->assertCount(1, $listeners[FormEvents::POST_SET_DATA]);
        $listener = $listeners[FormEvents::POST_SET_DATA][0];

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->never())->method('add');
        $listener(new FormEvent($form, null));
        $listener(new FormEvent($form, new AccountEnvironmentResume('c1', 'env', null)));

        $children = new ArrayIterator(
            [
                $this->buildChild('clusterName', []),
                $this->buildChild('envName', 'not an array'),
            ]
        );
        $form = $this->createMock(FormInterface::class);
        $form->method('rewind')->willReturnCallback($children->rewind(...));
        $form->method('valid')->willReturnCallback($children->valid(...));
        $form->method('current')->willReturnCallback($children->current(...));
        $form->method('key')->willReturnCallback($children->key(...));
        $form->method('next')->willReturnCallback($children->next(...));
        $form->expects($this->exactly(2))
            ->method('add')
            ->with($this->isString(), TextType::class, $this->isArray())
            ->willReturnSelf();

        $listener(new FormEvent($form, new AccountEnvironmentResume('c1', 'env', 'someId')));
    }

    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();
        $this->accountEnvironmentResumesType->configureOptions($resolver);

        $this->assertSame(
            AccountEnvironmentResume::class,
            $resolver->resolve(
                [
                    'subscriptionPlan' => new SubscriptionPlan('plan', 'Plan', []),
                    'clusterCatalog' => new ClusterCatalog([], []),
                ]
            )['data_class'],
        );
    }
}
