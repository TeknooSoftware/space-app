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

namespace Teknoo\Space\Extensions\Sample;

use Teknoo\East\Foundation\Extension\ExtensionInterface;
use Teknoo\East\Foundation\Extension\ModuleInterface;

/**
 * Fake extension class, implementing ExtensionInterface, used to test the extensions discovery,
 * the enabling and the disabling of an extension, without relying on a real extension installed
 * in the `extensions/` directory, which is not versioned.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
class Extension implements ExtensionInterface
{
    public static function create(): ExtensionInterface
    {
        return new self();
    }

    public function executeFor(ModuleInterface $module): ExtensionInterface
    {
        return $this;
    }

    public function __toString(): string
    {
        return self::class;
    }
}
