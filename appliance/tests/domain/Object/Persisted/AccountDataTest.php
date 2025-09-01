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

namespace Teknoo\Space\Tests\Unit\Object\Persisted;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\Persisted\AccountData;

/**
 * Class AccountDataTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountData::class)]
class AccountDataTest extends TestCase
{
    private AccountData $accountData;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $account = $this->createMock(Account::class);
        $legalName = '42';
        $streetAddress = '42';
        $zipCode = '42';
        $cityName = '42';
        $countryName = '42';
        $vatNumber = '42';
        $this->accountData = new AccountData(
            $account,
            $legalName,
            $streetAddress,
            $zipCode,
            $cityName,
            $countryName,
            $vatNumber,
        );
    }

    public function testSetAccount(): void
    {
        $expected = $this->createMock(Account::class);
        $property = new ReflectionClass(AccountData::class)
            ->getProperty('account');
        $this->accountData->setAccount($expected);
        $this->assertEquals($expected, $property->getValue($this->accountData));
    }

    public function testSetLegalName(): void
    {
        $expected = '42';
        $property = new ReflectionClass(AccountData::class)
            ->getProperty('legalName');
        $this->accountData->setLegalName($expected);
        $this->assertEquals($expected, $property->getValue($this->accountData));
    }

    public function testSetStreetAddress(): void
    {
        $expected = '42';
        $property = new ReflectionClass(AccountData::class)
            ->getProperty('streetAddress');
        $this->accountData->setStreetAddress($expected);
        $this->assertEquals($expected, $property->getValue($this->accountData));
    }

    public function testSetZipCode(): void
    {
        $expected = '42';
        $property = new ReflectionClass(AccountData::class)
            ->getProperty('zipCode');
        $this->accountData->setZipCode($expected);
        $this->assertEquals($expected, $property->getValue($this->accountData));
    }

    public function testSetCityName(): void
    {
        $expected = '42';
        $property = new ReflectionClass(AccountData::class)
            ->getProperty('cityName');
        $this->accountData->setCityName($expected);
        $this->assertEquals($expected, $property->getValue($this->accountData));
    }

    public function testSetCountryName(): void
    {
        $expected = '42';
        $property = new ReflectionClass(AccountData::class)
            ->getProperty('countryName');
        $this->accountData->setCountryName($expected);
        $this->assertEquals($expected, $property->getValue($this->accountData));
    }

    public function testSetVatNumber(): void
    {
        $expected = '42';
        $property = new ReflectionClass(AccountData::class)
            ->getProperty('vatNumber');
        $this->accountData->setVatNumber($expected);
        $this->assertEquals($expected, $property->getValue($this->accountData));
    }

    public function testVisit(): void
    {
        $final = null;
        $this->assertInstanceOf(
            AccountData::class,
            $this->accountData->visit([
                'legalName' => function ($value) use (&$final): void {
                    $final = $value;
                },
                'foo' => fn () => self::fail('Must be not called'),
            ]),
        );
        $this->assertEquals(
            '42',
            $final,
        );
    }
}
