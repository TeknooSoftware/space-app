<?php

/*
 * Teknoo Space.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
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
 * @license     http://teknoo.software/license/mit         MIT License
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
            $this->createMock(AccountPersistedVariable::class),
            $this->createMock(AccountPersistedVariable::class),
        ];

        $this->accountVarsType->mapDataToForms(
            $account,
            new ArrayIterator(
                [
                    'sets' => $this->createMock(FormInterface::class),
                ]
            ),
        );

        self::assertTrue(true);
    }

    public function testMapFormsToData(): void
    {
        $account = new SpaceAccount();
        $account->variables = [
            $this->createMock(AccountPersistedVariable::class),
            $this->createMock(AccountPersistedVariable::class),
        ];

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->any())
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
                                persistedVar: $this->createMock(AccountPersistedVariable::class),
                            ),
                            new JobVar(
                                id: null,
                                name: 'bar',
                                value: 'foo',
                                persisted: true,
                                secret: true,
                                wasSecret: false,
                                encryptionAlgorithm: 'rsa',
                                persistedVar: $this->createMock(AccountPersistedVariable::class)
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

        self::assertTrue(true);
    }
}
