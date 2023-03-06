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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Infrastructures\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use function substr;
use function uniqid;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Nonce extends AbstractExtension
{
    private static ?string $currentNonce = null;

    public function getFunctions(): array
    {
        return array(
            new TwigFunction('space_nonce', $this->getNonce(...))
        );
    }

    public function getName(): string
    {
        return 'space_nonce';
    }

    public function getNonce(): string
    {
        return self::$currentNonce ?? self::$currentNonce = substr(uniqid("", true), 0, 14);
    }
}
