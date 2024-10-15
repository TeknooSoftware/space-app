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

namespace Teknoo\Space\Infrastructures\Twig\SpaceExtension;

use Teknoo\East\Foundation\Extension\ManagerInterface;
use Teknoo\East\Foundation\Extension\ModuleInterface;
use Twig\Environment;
use Twig\TemplateWrapper;

use function array_map;
use function implode;

use const PHP_EOL;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Twig implements ModuleInterface
{
    private ?Environment $envTwig = null;

    private ?string $block = null;

    /**
     * @var array<int, TemplateWrapper>
     */
    private array $templates = [];

    public function __construct(
        private ManagerInterface $manager,
    ) {
    }

    public function run(Environment $env, string $block): self
    {
        $that = clone $this;
        $that->envTwig = $env;
        $that->block = $block;

        $this->manager->execute($that);

        return $that;
    }

    public function load(callable $getTemplate): self
    {
        if ($this->envTwig && !empty($template = $getTemplate($this->block))) {
            $this->templates[] = $this->envTwig->load($template);
        }

        return $this;
    }

    public function render(): string
    {
        return implode(
            PHP_EOL,
            array_map(
                fn (TemplateWrapper $template) => $template->render(),
                $this->templates,
            )
        );
    }
}
