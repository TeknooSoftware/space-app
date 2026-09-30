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

namespace Teknoo\Space\Infrastructures\Doctrine\Listener;

use Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs;
use Teknoo\East\Common\Contracts\User\AuthDataInterface;
use Teknoo\East\Common\Object\User;

use function is_a;

/**
 * Doctrine ODM listener to keep readable the auth data persisted with a legacy class name. User's auth data are
 * embedded documents, stored with their class name in the discriminator field `type`. When a class is moved (like
 * `ApiKeysAuth`, from Space to East Common), already persisted documents keep the old name.
 *
 * This listener adds a discriminator map to the field `authData` of the User's metadata :
 * - all mapped auth data classes, with their class name as value, like without map, so nothing changes for them
 * - then the aliases, legacy names mapped to the current classes.
 *
 * Doctrine ODM writes the first value found in the map for a class, so auth data are always written with their current
 * class name : a legacy document is rewritten with the new name when its user is updated.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class AuthDataDiscriminatorMapListener
{
    private const string FIELD_NAME = 'authData';

    /**
     * @param array<string, class-string<AuthDataInterface>> $aliases legacy discriminator values => current classes
     */
    public function __construct(
        private readonly array $aliases,
    ) {
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $metadata = $eventArgs->getClassMetadata();
        if (
            !is_a($metadata->getName(), User::class, true)
            || !isset($metadata->fieldMappings[self::FIELD_NAME])
        ) {
            return;
        }

        $driver = $eventArgs->getDocumentManager()->getConfiguration()->getMetadataDriverImpl();

        $discriminatorMap = [];
        foreach ($driver?->getAllClassNames() ?? [] as $className) {
            if (is_a($className, AuthDataInterface::class, true)) {
                $discriminatorMap[$className] = $className;
            }
        }

        //Aliases must be after class names : Doctrine writes the first value found for a class
        foreach ($this->aliases as $legacyName => $className) {
            $discriminatorMap[$legacyName] ??= $className;
        }

        $metadata->fieldMappings[self::FIELD_NAME]['discriminatorMap'] = $discriminatorMap;
        $metadata->associationMappings[self::FIELD_NAME]['discriminatorMap'] = $discriminatorMap;
    }
}
