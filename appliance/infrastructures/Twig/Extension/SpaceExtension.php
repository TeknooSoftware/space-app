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

namespace Teknoo\Space\Infrastructures\Twig\Extension;

use Teknoo\Space\Infrastructures\Twig\SpaceExtension\Twig as TwigExtension;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SpaceExtension extends AbstractExtension
{
    public function __construct(
        private TwigExtension $twig,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                name: 'space_extension',
                callable: $this->runExtension(...),
                options: [
                    'needs_environment' => true,
                    'is_safe' => ['html', 'json', 'js'],
                ],
            )
        ];
    }

    public function getName(): string
    {
        return 'space_extension';
    }

    public function runExtension(Environment $env, string $block): string
    {
        return $this->twig->run($env, $block)->render();
    }
}
