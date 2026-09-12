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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Form\Type\Search;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Infrastructures\Symfony\Form\Type\Search\AccountClusterSearchType;
use Teknoo\Space\Object\DTO\Search;

/**
 * Class AccountClusterSearchTypeTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountClusterSearchType::class)]
class AccountClusterSearchTypeTest extends TestCase
{
    private AccountClusterSearchType $accountClusterSearchType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();


        $this->accountClusterSearchType = new AccountClusterSearchType();
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertIsString($this->accountClusterSearchType->getBlockPrefix());
    }

    public function testBuildForm(): void
    {
        $this->accountClusterSearchType->buildForm(
            $this->createStub(FormBuilderInterface::class),
            [],
        );
        $this->assertTrue(true);
    }

    public function testConfigureOptions(): void
    {
        $this->accountClusterSearchType->configureOptions(
            $this->createStub(OptionsResolver::class),
        );
        $this->assertTrue(true);
    }

    public function testBuildFormWithManagerPostSubmitListener(): void
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

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with($this->callback(fn (array $workPlan): bool => isset($workPlan['criteria'])))
            ->willReturnSelf();

        $this->accountClusterSearchType->buildForm($builder, ['manager' => $manager]);

        $this->assertCount(1, $listeners[FormEvents::POST_SUBMIT]);
        $listeners[FormEvents::POST_SUBMIT][0](
            new FormEvent($this->createStub(FormInterface::class), new Search('foo')),
        );
    }
}
