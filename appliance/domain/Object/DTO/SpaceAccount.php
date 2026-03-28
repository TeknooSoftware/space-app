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
use Teknoo\East\Foundation\Normalizer\Object\AutoTrait;
use Teknoo\East\Foundation\Normalizer\Object\ClassGroup;
use Teknoo\East\Foundation\Normalizer\Object\Normalize;
use Teknoo\East\Foundation\Normalizer\Object\NormalizableInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\Persisted\AccountData;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;

use function array_keys;
use function array_values;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[ClassGroup('default', 'api', 'crud', 'digest')]
class SpaceAccount implements IdentifiedObjectInterface, NormalizableInterface, \Stringable
{
    use AutoTrait;

    /**
     * @param iterable<AccountPersistedVariable>|AccountPersistedVariable[] $variables
     * @param AccountEnvironmentResume[] $environments
     */
    public function __construct(
        #[Normalize(['default', 'crud', 'digest'], loader: '@lazy')]
        public Account $account = new Account(),
        #[Normalize('crud', loader: '@lazy')]
        public ?AccountData $accountData = null,
        #[Normalize('crud_variables', loader: '@lazy')]
        public iterable $variables = [],
        #[Normalize(
            'crud_environments',
            loader: static function (self $that): iterable {
                return array_values($that->environments ?? []);
            }
        )]
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
}
