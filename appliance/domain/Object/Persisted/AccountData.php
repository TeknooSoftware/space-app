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
use Teknoo\East\Foundation\Normalizer\EastNormalizerInterface;
use Teknoo\East\Foundation\Normalizer\Object\GroupsTrait;
use Teknoo\East\Foundation\Normalizer\Object\NormalizableInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\Traits\ExportConfigurationsTrait;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountData implements
    IdentifiedObjectInterface,
    TimestampableInterface,
    VisitableInterface,
    NormalizableInterface
{
    use ObjectTrait;
    use GroupsTrait;
    use ExportConfigurationsTrait;

    private ?string $vatNumber = '';

    /**
     * @var array<string, string[]>
     */
    private static array $exportConfigurations = [
        '@class' => ['default', 'crud'],
        'legalName' => ['default', 'crud'],
        'streetAddress' => ['crud'],
        'zipCode' => ['crud'],
        'cityName' => ['crud'],
        'countryName' => ['crud'],
        'vatNumber' => ['crud'],
    ];

    public function __construct(
        private Account $account,
        private string $legalName = '',
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

    public function visit($visitors): VisitableInterface
    {
        $fields = ['legalName', 'streetAddress', 'zipCode', 'cityName', 'countryName', 'vatNumber'];
        foreach ($fields as $keyName) {
            if (isset($visitors[$keyName])) {
                $visitors[$keyName]($this->{$keyName});
            }
        }

        return $this;
    }

    public function exportToMeData(EastNormalizerInterface $normalizer, array $context = []): NormalizableInterface
    {
        $data = [
            '@class' => self::class,
            'legalName' => $this->legalName,
            'streetAddress' => $this->streetAddress,
            'zipCode' => $this->zipCode,
            'cityName' => $this->cityName,
            'countryName' => $this->countryName,
            'vatNumber' => $this->vatNumber,
        ];

        $this->setGroupsConfiguration(self::$exportConfigurations);

        $normalizer->injectData(
            $this->filterExport(
                data: $data,
                groups: (array) ($context['groups'] ?? ['default']),
            )
        );

        return $this;
    }
}
