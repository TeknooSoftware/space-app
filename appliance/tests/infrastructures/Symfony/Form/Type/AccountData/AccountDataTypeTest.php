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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Form\Type\AccountData;

use ArrayIterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Infrastructures\Symfony\Form\Type\AccountData\AccountDataType;
use Teknoo\Space\Object\Config\SubscriptionPlan;
use Teknoo\Space\Object\Config\SubscriptionPlanCatalog;
use Teknoo\Space\Object\Persisted\AccountData;

/**
 * Class AccountDataTypeTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountDataType::class)]
class AccountDataTypeTest extends TestCase
{
    private AccountDataType $accountDataType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->accountDataType = new AccountDataType(
            new SubscriptionPlanCatalog(
                [
                    'plan1' => new SubscriptionPlan('plan1', 'Plan 1', []),
                    'plan2' => new SubscriptionPlan('plan2', 'Plan 2', []),
                ]
            )
        );
    }

    public function testBuildForm(): void
    {
        $this->accountDataType->buildForm(
            $this->createStub(FormBuilderInterface::class),
            [],
        );
        $this->assertTrue(true);
    }

    private function captureDataMapper(bool $canUpdateSubscription): DataMapperInterface
    {
        $mapper = null;
        $added = [];
        $builder = $this->createStub(FormBuilderInterface::class);
        $builder->method('add')
            ->willReturnCallback(
                function (string $child, ?string $type = null, array $options = []) use (&$added, $builder) {
                    $added[$child] = $options;

                    return $builder;
                }
            );
        $builder->method('setDataMapper')
            ->willReturnCallback(
                function (DataMapperInterface $dataMapper) use (&$mapper, $builder) {
                    $mapper = $dataMapper;

                    return $builder;
                }
            );

        $this->accountDataType->buildForm($builder, ['canUpdateSubscription' => $canUpdateSubscription]);

        $this->assertSame(['Plan 1' => 'plan1', 'Plan 2' => 'plan2'], $added['subscriptionPlan']['choices']);
        $this->assertSame(!$canUpdateSubscription, $added['subscriptionPlan']['disabled']);
        $this->assertInstanceOf(DataMapperInterface::class, $mapper);

        return $mapper;
    }

    private function buildFormStub(?string $data): FormInterface&Stub
    {
        $form = $this->createStub(FormInterface::class);
        $form->method('getData')->willReturn($data);

        return $form;
    }

    /**
     * @return array<string, FormInterface&Stub>
     */
    private function buildForms(): array
    {
        return [
            'legalName' => $this->buildFormStub('legal'),
            'streetAddress' => $this->buildFormStub('street'),
            'zipCode' => $this->buildFormStub('zip'),
            'cityName' => $this->buildFormStub('city'),
            'countryName' => $this->buildFormStub('country'),
            'vatNumber' => $this->buildFormStub(null),
            'subscriptionPlan' => $this->buildFormStub('plan2'),
        ];
    }

    public function testDataMapperMapDataToForms(): void
    {
        $mapper = $this->captureDataMapper(false);

        $mapper->mapDataToForms(null, new ArrayIterator([]));

        $legalName = $this->createMock(FormInterface::class);
        $legalName->expects($this->once())->method('setData')->with('legal')->willReturnSelf();
        $forms = $this->buildForms();
        $forms['legalName'] = $legalName;

        $mapper->mapDataToForms(
            new AccountData(new Account(), legalName: 'legal', subscriptionPlan: 'plan1'),
            new ArrayIterator($forms),
        );
    }

    private function readProperty(AccountData $accountData, string $property): mixed
    {
        $value = null;
        $accountData->visit(
            $property,
            static function (mixed $read) use (&$value): void {
                $value = $read;
            }
        );

        return $value;
    }

    public function testDataMapperMapFormsToData(): void
    {
        $mapper = $this->captureDataMapper(false);

        $notAccountData = null;
        $mapper->mapFormsToData(new ArrayIterator([]), $notAccountData);
        $this->assertNull($notAccountData);

        $accountData = new AccountData(new Account(), subscriptionPlan: 'plan1');
        $mapper->mapFormsToData(new ArrayIterator($this->buildForms()), $accountData);
        $this->assertSame('plan1', $this->readProperty($accountData, 'subscriptionPlan'));

        $mapper = $this->captureDataMapper(true);
        $mapper->mapFormsToData(new ArrayIterator($this->buildForms()), $accountData);
        $this->assertSame('plan2', $this->readProperty($accountData, 'subscriptionPlan'));
        $this->assertSame('legal', $this->readProperty($accountData, 'legalName'));
    }

    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();
        $this->accountDataType->configureOptions($resolver);

        $resolved = $resolver->resolve([]);
        $this->assertSame(AccountData::class, $resolved['data_class']);
        $this->assertFalse($resolved['canUpdateSubscription']);
    }
}
