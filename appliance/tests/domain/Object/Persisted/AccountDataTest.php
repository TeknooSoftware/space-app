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
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Normalizer\EastNormalizerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Object\Persisted\AccountData;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

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

    private Account&MockObject $account;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->account = $this->createMock(Account::class);
        $legalName = '42';
        $streetAddress = '42';
        $zipCode = '42';
        $cityName = '42';
        $countryName = '42';
        $vatNumber = '42';
        $this->accountData = new AccountData(
            $this->account,
            $legalName,
            $streetAddress,
            $zipCode,
            $cityName,
            $countryName,
            $vatNumber,
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSetAccount(): void
    {
        $newAccount = $this->createStub(Account::class);
        $result = $this->accountData->setAccount($newAccount);

        $this->assertInstanceOf(AccountData::class, $result);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSetLegalName(): void
    {
        $result = $this->accountData->setLegalName('New Legal Name');

        $this->assertInstanceOf(AccountData::class, $result);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSetStreetAddress(): void
    {
        $result = $this->accountData->setStreetAddress('123 Main St');

        $this->assertInstanceOf(AccountData::class, $result);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSetZipCode(): void
    {
        $result = $this->accountData->setZipCode('12345');

        $this->assertInstanceOf(AccountData::class, $result);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSetCityName(): void
    {
        $result = $this->accountData->setCityName('Paris');

        $this->assertInstanceOf(AccountData::class, $result);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSetCountryName(): void
    {
        $result = $this->accountData->setCountryName('France');

        $this->assertInstanceOf(AccountData::class, $result);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSetVatNumber(): void
    {
        $result = $this->accountData->setVatNumber('FR12345678901');

        $this->assertInstanceOf(AccountData::class, $result);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSetVatNumberWithNull(): void
    {
        $result = $this->accountData->setVatNumber(null);

        $this->assertInstanceOf(AccountData::class, $result);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSetSubscriptionPlan(): void
    {
        $result = $this->accountData->setSubscriptionPlan('premium');

        $this->assertInstanceOf(AccountData::class, $result);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSetSubscriptionPlanWithNull(): void
    {
        $result = $this->accountData->setSubscriptionPlan(null);

        $this->assertInstanceOf(AccountData::class, $result);
    }

    #[AllowMockObjectsWithoutExpectations]
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

    #[AllowMockObjectsWithoutExpectations]
    public function testExportToMeData(): void
    {
        $normalizer = $this->createMock(EastNormalizerInterface::class);

        $normalizer->expects($this->once())
            ->method('injectData')
            ->with($this->isArray());

        $result = $this->accountData->exportToMeData($normalizer, []);

        $this->assertInstanceOf(AccountData::class, $result);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testExportToMeDataWithGroups(): void
    {
        $normalizer = $this->createMock(EastNormalizerInterface::class);

        $normalizer->expects($this->once())
            ->method('injectData')
            ->with($this->isArray());

        $result = $this->accountData->exportToMeData($normalizer, ['groups' => ['crud']]);

        $this->assertInstanceOf(AccountData::class, $result);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testVerifyAccessToUser(): void
    {
        $user = $this->createStub(User::class);
        $promise = $this->createMock(PromiseInterface::class);

        $this->account->expects($this->once())
            ->method('__call')
            ->with('verifyAccessToUser');

        $result = $this->accountData->verifyAccessToUser($user, $promise);

        $this->assertInstanceOf(AccountData::class, $result);
    }
}
