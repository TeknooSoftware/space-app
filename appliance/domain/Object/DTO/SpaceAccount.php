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

namespace Teknoo\Space\Object\DTO;

use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Foundation\Normalizer\EastNormalizerInterface;
use Teknoo\East\Foundation\Normalizer\Object\GroupsTrait;
use Teknoo\East\Foundation\Normalizer\Object\NormalizableInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\Traits\ExportConfigurationsTrait;
use Teknoo\Space\Object\Persisted\AccountData;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;

use function array_values;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SpaceAccount implements IdentifiedObjectInterface, NormalizableInterface, \Stringable
{
    use GroupsTrait;
    use ExportConfigurationsTrait;

    /**
     * @var array<string, string[]>
     */
    private static array $exportConfigurations = [
        '@class' => ['default', 'api', 'crud', 'digest'],
        'account' => ['default', 'crud', 'digest'],
        'accountData' => ['crud'],
        'variables' => ['crud_variables'],
        'environments' => ['crud_environments'],
    ];

    /**
     * @param iterable<AccountPersistedVariable>|AccountPersistedVariable[] $variables
     * @param AccountEnvironmentResume[] $environments
     */
    public function __construct(
        public Account $account = new Account(),
        public ?AccountData $accountData = null,
        public iterable $variables = [],
        public ?array $environments = null,
    ) {
        if (null === $this->accountData) {
            $this->accountData = new AccountData($this->account);
        }
    }

    public function getId(): string
    {
        return $this->account->getId();
    }

    public function __toString(): string
    {
        return (string) $this->account;
    }

    public function exportToMeData(EastNormalizerInterface $normalizer, array $context = []): NormalizableInterface
    {
        $data = [
            '@class' => self::class,
            'account' => fn (): Account => $this->account,
            'accountData' => fn (): ?AccountData => $this->accountData,
            'variables' => fn (): iterable => $this->variables,
            'environments' => fn () => array_values($this->environments ?? []),
        ];

        $this->setGroupsConfiguration(self::$exportConfigurations);

        $normalizer->injectData(
            $this->filterExport(
                data: $data,
                groups: (array) ($context['groups'] ?? ['default']),
                lazyData: true,
            )
        );

        return $this;
    }
}
