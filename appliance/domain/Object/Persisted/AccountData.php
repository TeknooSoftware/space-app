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

namespace Teknoo\Space\Object\Persisted;

use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\TimestampableInterface;
use Teknoo\East\Common\Contracts\Object\VisitableInterface;
use Teknoo\East\Common\Object\ObjectTrait;
use Teknoo\East\Paas\Object\Account;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountData implements IdentifiedObjectInterface, TimestampableInterface, VisitableInterface
{
    use ObjectTrait;

    private ?string $vatNumber = '';

    public function __construct(
        private Account $account,
        private string $billingName = '',
        private string $streetAddress = '',
        private string $zipCode = '',
        private string $cityName = '',
        private string $countryName = '',
        ?string $vatNumber = '',
    ) {
        //Issue with doctine
        $this->vatNumber = $vatNumber;
    }

    public function setAccount(Account $account): AccountData
    {
        $this->account = $account;

        return $this;
    }

    public function setBillingName(string $billingName): AccountData
    {
        $this->billingName = $billingName;

        return $this;
    }

    public function setStreetAddress(string $streetAddress): AccountData
    {
        $this->streetAddress = $streetAddress;

        return $this;
    }

    public function setZipCode(string $zipCode): AccountData
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function setCityName(string $cityName): AccountData
    {
        $this->cityName = $cityName;

        return $this;
    }

    public function setCountryName(string $countryName): AccountData
    {
        $this->countryName = $countryName;

        return $this;
    }

    public function setVatNumber(?string $vatNumber): AccountData
    {
        $this->vatNumber = $vatNumber;

        return $this;
    }

    public function visit($visitors): VisitableInterface
    {
        $fields = ['billingName', 'streetAddress', 'zipCode', 'cityName', 'countryName', 'vatNumber'];
        foreach ($fields as $keyName) {
            if (isset($visitors[$keyName])) {
                $visitors[$keyName]($this->{$keyName});
            }
        }

        return $this;
    }
}
