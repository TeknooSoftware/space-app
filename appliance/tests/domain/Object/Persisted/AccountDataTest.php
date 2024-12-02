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

    private Account|MockObject $account;

    private string $legalName;

    private string $streetAddress;

    private string $zipCode;

    private string $cityName;

    private string $countryName;

    private string $vatNumber;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->account = $this->createMock(Account::class);
        $this->legalName = '42';
        $this->streetAddress = '42';
        $this->zipCode = '42';
        $this->cityName = '42';
        $this->countryName = '42';
        $this->vatNumber = '42';
        $this->accountData = new AccountData(
            $this->account,
            $this->legalName,
            $this->streetAddress,
            $this->zipCode,
            $this->cityName,
            $this->countryName,
            $this->vatNumber,
        );
    }

    public function testSetAccount(): void
    {
        $expected = $this->createMock(Account::class);
        $property = (new ReflectionClass(AccountData::class))
            ->getProperty('account');
        $property->setAccessible(true);
        $this->accountData->setAccount($expected);
        self::assertEquals($expected, $property->getValue($this->accountData));
    }

    public function testSetLegalName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountData::class))
            ->getProperty('legalName');
        $property->setAccessible(true);
        $this->accountData->setLegalName($expected);
        self::assertEquals($expected, $property->getValue($this->accountData));
    }

    public function testSetStreetAddress(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountData::class))
            ->getProperty('streetAddress');
        $property->setAccessible(true);
        $this->accountData->setStreetAddress($expected);
        self::assertEquals($expected, $property->getValue($this->accountData));
    }

    public function testSetZipCode(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountData::class))
            ->getProperty('zipCode');
        $property->setAccessible(true);
        $this->accountData->setZipCode($expected);
        self::assertEquals($expected, $property->getValue($this->accountData));
    }

    public function testSetCityName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountData::class))
            ->getProperty('cityName');
        $property->setAccessible(true);
        $this->accountData->setCityName($expected);
        self::assertEquals($expected, $property->getValue($this->accountData));
    }

    public function testSetCountryName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountData::class))
            ->getProperty('countryName');
        $property->setAccessible(true);
        $this->accountData->setCountryName($expected);
        self::assertEquals($expected, $property->getValue($this->accountData));
    }

    public function testSetVatNumber(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(AccountData::class))
            ->getProperty('vatNumber');
        $property->setAccessible(true);
        $this->accountData->setVatNumber($expected);
        self::assertEquals($expected, $property->getValue($this->accountData));
    }

    public function testVisit(): void
    {
        $final = null;
        self::assertInstanceOf(
            AccountData::class,
            $this->accountData->visit([
                'legalName' => function ($value) use (&$final) {
                    $final = $value;
                },
                'foo' => fn () => self::fail('Must be not called'),
            ]),
        );
        self::assertEquals(
            '42',
            $final,
        );
    }
}
