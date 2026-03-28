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

namespace Teknoo\Space\Object\Persisted;

use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\TimestampableInterface;
use Teknoo\East\Common\Contracts\Object\VisitableInterface;
use Teknoo\East\Common\Object\ObjectTrait;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Object\VisitableTrait;
use Teknoo\East\Foundation\Normalizer\Object\AutoTrait;
use Teknoo\East\Foundation\Normalizer\Object\ClassGroup;
use Teknoo\East\Foundation\Normalizer\Object\Normalize;
use Teknoo\East\Foundation\Normalizer\Object\NormalizableInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Contracts\Object\AccountComponentInterface;

use function array_flip;
use function array_intersect_key;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[ClassGroup('default', 'crud')]
class AccountData implements
    IdentifiedObjectInterface,
    TimestampableInterface,
    VisitableInterface,
    NormalizableInterface,
    AccountComponentInterface
{
    use ObjectTrait;
    use AutoTrait;
    use VisitableTrait {
        VisitableTrait::runVisit as realRunVisit;
    }

    #[Normalize('crud')]
    private ?string $vatNumber = '';

    public function __construct(
        private Account $account,
        #[Normalize(['default', 'crud'])]
        private string $legalName = '',
        #[Normalize('crud')]
        private string $streetAddress = '',
        #[Normalize('crud')]
        private string $zipCode = '',
        #[Normalize('crud')]
        private string $cityName = '',
        #[Normalize('crud')]
        private string $countryName = '',
        ?string $vatNumber = '',
        #[Normalize(
            ['default', 'crud'],
            loader: static function (self $that): string {
                return (string) $that->subscriptionPlan;
            }
        )]
        private ?string $subscriptionPlan = null,
    ) {
        //Issue with doctine
        $this->vatNumber = $vatNumber;
    }

    public function setAccount(Account $account): AccountData
    {
        $this->account = $account;

        return $this;
    }

    public function setLegalName(string $legalName): AccountData
    {
        $this->legalName = $legalName;

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

    public function setSubscriptionPlan(?string $subscriptionPlan): AccountData
    {
        $this->subscriptionPlan = $subscriptionPlan;

        return $this;
    }

    /**
     * @param array<string, callable> $visitors
     */
    private function runVisit(array &$visitors): void
    {
        $visitors = array_intersect_key(
            $visitors,
            array_flip(
                ['legalName', 'streetAddress', 'zipCode', 'cityName', 'countryName', 'vatNumber', 'subscriptionPlan'],
            ),
        );

        $this->realRunVisit($visitors);
    }

    public function verifyAccessToUser(User $user, PromiseInterface $promise): AccountComponentInterface
    {
        $this->account->verifyAccessToUser($user, $promise);

        return $this;
    }
}
