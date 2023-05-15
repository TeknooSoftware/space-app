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
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Object\Persisted;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;

/**
 * Class AccountPersistedVariableTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Object\Persisted\AccountPersistedVariable
 */
class AccountPersistedVariableTest extends TestCase
{
    private AccountPersistedVariable $accountPersistedVariable;

    private Account|MockObject $account;

    private string $id;

    private string $name;

    private string $value;

    private string $environmentName;

    private bool $secret;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->account = $this->createMock(Account::class);
        $this->id = '42';
        $this->name = '42';
        $this->value = '42';
        $this->environmentName = '42';
        $this->secret = true;
        $this->accountPersistedVariable = new AccountPersistedVariable(
            $this->account,
            $this->id,
            $this->name,
            $this->value,
            $this->environmentName,
            $this->secret,
        );
    }

    public function testGetAccount(): void
    {
        $expected = $this->createMock(Account::class);
        $property = (new ReflectionClass(AccountPersistedVariable::class))
            ->getProperty('account');
        $property->setAccessible(true);
        $property->setValue($this->accountPersistedVariable, $expected);
        self::assertEquals($expected, $this->accountPersistedVariable->getAccount());
    }

    public function testGetName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountPersistedVariable::class))
            ->getProperty('name');
        $property->setAccessible(true);
        $property->setValue($this->accountPersistedVariable, $expected);
        self::assertEquals($expected, $this->accountPersistedVariable->getName());
    }

    public function testGetValue(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountPersistedVariable::class))
            ->getProperty('value');
        $property->setAccessible(true);
        $property->setValue($this->accountPersistedVariable, $expected);
        self::assertEquals($expected, $this->accountPersistedVariable->getValue());
    }

    public function testGetEnvironmentName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountPersistedVariable::class))
            ->getProperty('environmentName');
        $property->setAccessible(true);
        $property->setValue($this->accountPersistedVariable, $expected);
        self::assertEquals($expected, $this->accountPersistedVariable->getEnvironmentName());
    }

    public function testIsSecret(): void
    {
        self::assertIsBool(
            $this->accountPersistedVariable->isSecret(),
        );
    }
}
