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

namespace Teknoo\Space\App\Config;

use ArrayObject;
use Psr\Container\ContainerInterface;
use Teknoo\East\Paas\Contracts\Hook\HooksCollectionInterface;
use Teknoo\East\Paas\Infrastructures\ProjectBuilding\ComposerHook;
use Teknoo\East\Paas\Infrastructures\ProjectBuilding\MakeHook;
use Teknoo\East\Paas\Infrastructures\ProjectBuilding\NpmHook;
use Teknoo\East\Paas\Infrastructures\ProjectBuilding\PipHook;
use Teknoo\East\Paas\Infrastructures\ProjectBuilding\SfConsoleHook;
use Traversable;

return [
    'teknoo.space.hook.collection' => static function (): ArrayObject {
        return new ArrayObject([
            'composer' => ComposerHook::class,
            'npm' => NpmHook::class,
            'pip' => PipHook::class,
            'make' => MakeHook::class,
            'symfony_console' => SfConsoleHook::class,
        ]);
    },

    HooksCollectionInterface::class => static function (ContainerInterface $container): HooksCollectionInterface {
        return new class (
            $container,
            $container->get('teknoo.space.hook.collection'),
        ) implements HooksCollectionInterface {
            /**
             * @param iterable<string> $hooksNames
             */
            public function __construct(
                private ContainerInterface $container,
                private iterable $hooksNames,
            ) {
            }

            public function getIterator(): Traversable
            {
                foreach ($this->hooksNames as $name => $class) {
                    $key = "teknoo.east.paas.{$name}.path";
                    if (
                        $this->container->has($key)
                        && !empty($this->container->get($key))
                    ) {
                        yield $name => $this->container->get($class);
                    }
                }
            }
        };
    },
];
