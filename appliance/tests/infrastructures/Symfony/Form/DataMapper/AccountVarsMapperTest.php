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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Form\DataMapper;

use ArrayIterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Teknoo\Space\Infrastructures\Symfony\Form\DataMapper\AbstractVarsMapper;
use Teknoo\Space\Infrastructures\Symfony\Form\DataMapper\AccountVarsMapper;
use Teknoo\Space\Object\DTO\JobVar;
use Teknoo\Space\Object\DTO\JobVarsSet;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;

/**
 * Class AccountVarsMapperTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AbstractVarsMapper::class)]
#[CoversClass(AccountVarsMapper::class)]
class AccountVarsMapperTest extends TestCase
{
    private AccountVarsMapper $accountVarsType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->accountVarsType = new AccountVarsMapper();
    }

    public function testMapDataToForms(): void
    {
        $account = new SpaceAccount();
        $account->variables = [
            $this->createStub(AccountPersistedVariable::class),
            $this->createStub(AccountPersistedVariable::class),
        ];

        $this->accountVarsType->mapDataToForms(
            $account,
            new ArrayIterator(
                [
                    'sets' => $this->createStub(FormInterface::class),
                ]
            ),
        );

        $this->assertTrue(true);
    }

    public function testMapFormsToData(): void
    {
        $account = new SpaceAccount();
        $account->variables = [
            $this->createStub(AccountPersistedVariable::class),
            $this->createStub(AccountPersistedVariable::class),
        ];

        $form = $this->createStub(FormInterface::class);
        $form
            ->method('getData')
            ->willReturn(
                [
                    new JobVarsSet(
                        envName: 'foo',
                        variables: [
                            new JobVar(
                                id: 'foo',
                                name: 'bar',
                                value: 'foo',
                                persisted: false,
                                secret: true,
                                wasSecret: true,
                                encryptionAlgorithm: 'rsa',
                                persistedVar: $this->createStub(AccountPersistedVariable::class),
                            ),
                            new JobVar(
                                id: null,
                                name: 'bar',
                                value: 'foo',
                                persisted: true,
                                secret: true,
                                wasSecret: false,
                                encryptionAlgorithm: 'rsa',
                                persistedVar: $this->createStub(AccountPersistedVariable::class)
                            ),
                        ]
                    )
                ]
            );

        $this->accountVarsType->mapFormsToData(
            new ArrayIterator(
                [
                    'sets' => $form,
                ]
            ),
            $account,
        );

        $this->assertTrue(true);
    }

    public function testMapDataToFormsWithoutSpaceObject(): void
    {
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->never())->method('setData');

        $this->accountVarsType->mapDataToForms(null, new ArrayIterator(['sets' => $form]));
    }

    public function testMapFormsToDataWithoutSpaceObject(): void
    {
        $viewData = null;
        $this->accountVarsType->mapFormsToData(new ArrayIterator([]), $viewData);

        $this->assertNull($viewData);
    }

    public function testMapFormsToDataRestoreSecretValueFromPersistedVariable(): void
    {
        $persisted = $this->createStub(AccountPersistedVariable::class);
        $persisted->method('getId')->willReturn('v1');
        $persisted->method('getValue')->willReturn('secret-value');
        $persisted->method('getEncryptionAlgorithm')->willReturn('rsa');

        $account = new SpaceAccount();
        $account->variables = [$persisted];

        $variable = new JobVar(
            id: 'v1',
            name: 'name',
            value: '',
            persisted: true,
            secret: false,
            wasSecret: true,
        );

        $form = $this->createStub(FormInterface::class);
        $form->method('getData')
            ->willReturn([new JobVarsSet(envName: 'foo', variables: [$variable])]);

        $this->accountVarsType->mapFormsToData(new ArrayIterator(['sets' => $form]), $account);

        $this->assertSame('rsa', $variable->encryptionAlgorithm);
        $this->assertSame('secret-value', $variable->value);
        $this->assertCount(1, $account->variables);
        $this->assertInstanceOf(AccountPersistedVariable::class, $account->variables[0]);
    }
}
