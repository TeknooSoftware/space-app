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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Infrastructures\Symfony\Form\DataMapper;

use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountPersistedVariable;
use Teknoo\Space\Object\Persisted\ProjectPersistedVariable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AccountVarsMapper extends AbstractVarsMapper
{
    /**
     * @param SpaceAccount $parent
     */
    protected function buildVariable(
        mixed $parent,
        ?string $id,
        string $name,
        ?string $value,
        string $envName,
        bool $secret,
        ?string $encryptionAlgorithm,
        bool $needEncryption,
    ): AccountPersistedVariable|ProjectPersistedVariable {
        return new AccountPersistedVariable(
            account: $parent->account,
            id: $id,
            name: $name,
            value: $value,
            envName: $envName,
            secret: $secret,
            encryptionAlgorithm: $encryptionAlgorithm,
            needEncryption: $needEncryption,
        );
    }
}
