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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Form\DataMapper;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Teknoo\Space\Infrastructures\Symfony\Form\DataMapper\AccountVarsMapper;
use Teknoo\Space\Object\DTO\JobVar;
use Teknoo\Space\Object\DTO\JobVarsSet;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;

/**
 * Class AccountVarsMapperTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Infrastructures\Symfony\Form\DataMapper\AccountVarsMapper
 * @covers \Teknoo\Space\Infrastructures\Symfony\Form\DataMapper\AbstractVarsMapper
 */
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

        self::assertInstanceOf(
            AccountVarsMapper::class,
            $this->accountVarsType->mapDataToForms(
                $account,
                new ArrayIterator(
                    [
                        'sets' => $this->createMock(FormInterface::class),
                    ]
                ),
            ),
        );
    }

    public function testMapFormsToData(): void
    {
        $account = new SpaceAccount();
        $account->variables = [
            $this->createMock(AccountPersistedVariable::class),
            $this->createMock(AccountPersistedVariable::class),
        ];

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::any())
            ->method('getData')
            ->willReturn(
                [
                    new JobVarsSet(
                        'foo',
                        [
                            new JobVar(
                                'foo',
                                'bar',
                                'foo',
                                false,
                                true,
                                true,
                                $this->createMock(AccountPersistedVariable::class),
                            ),
                            new JobVar(
                                null,
                                'bar',
                                'foo',
                                true,
                                true,
                                false,
                                $this->createMock(AccountPersistedVariable::class)
                            ),
                        ]
                    )
                ]
            );
        self::assertInstanceOf(
            AccountVarsMapper::class,
            $this->accountVarsType->mapFormsToData(
                new ArrayIterator(
                    [
                        'sets' => $form,
                    ]
                ),
                $account,
            ),
        );
    }
}
