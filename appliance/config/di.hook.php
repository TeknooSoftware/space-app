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

use DomainException;
use Psr\Container\ContainerInterface;
use Teknoo\East\Paas\Contracts\Hook\HookInterface;
use Teknoo\East\Paas\Contracts\Hook\HooksCollectionInterface;
use Teknoo\East\Paas\Hook\HooksCollection;
use Teknoo\East\Paas\Infrastructures\ProjectBuilding\ComposerHook;
use Teknoo\East\Paas\Infrastructures\ProjectBuilding\Contracts\ProcessFactoryInterface;
use Teknoo\East\Paas\Infrastructures\ProjectBuilding\MakeHook;
use Teknoo\East\Paas\Infrastructures\ProjectBuilding\NpmHook;
use Teknoo\East\Paas\Infrastructures\ProjectBuilding\PipHook;
use Teknoo\East\Paas\Infrastructures\ProjectBuilding\SfConsoleHook;

use function class_exists;
use function is_a;
use function is_string;

return [
    HooksCollectionInterface::class => static function (ContainerInterface $container): HooksCollectionInterface {
        $definitions = [];
        if ($container->has('teknoo.space.hooks_collection.definitions')) {
            $definitions = $container->get('teknoo.space.hooks_collection.definitions');
        }

        $factory = $container->get(ProcessFactoryInterface::class);
        $defaultTimeout = 240.0;

        $collections = [];
        foreach ($definitions as $definition) {
            if (empty($definition['name']) || !is_string($definition['name'])) {
                throw new DomainException(
                    'Wrong hooks collection definition : at least one of hook is missing the name',
                );
            }
            $name = $definition['name'];

            if (empty($definition['type']) || !is_string($definition['type'])) {
                throw new DomainException(
                    "Wrong hooks collection definition : `$name` hook is missing the type",
                );
            }
            $type = $definition['type'];

            if (empty($definition['command'])) {
                throw new DomainException(
                    "Wrong hooks collection definition : `$name` hook is missing the command",
                );
            }
            $command = $definition['command'];

            $timeout = $definition['timeout'] ?? $defaultTimeout;

            if (class_exists($type) && is_a($type, HookInterface::class, true)) {
                $className = $type;
                $collections[$name] = new $className(
                    $command,
                    $timeout,
                    $factory,
                );

                continue;
            }

            $collections[$definition['name']] = match ($type) {
                'composer' => new ComposerHook(
                    command: $command,
                    timeout: $timeout,
                    factory: $factory,
                ),
                'npm' => new NpmHook(
                    command: $command,
                    timeout: $timeout,
                    factory: $factory,
                ),
                'pip' => new PipHook(
                    command: $command,
                    timeout: $timeout,
                    factory: $factory,
                ),
                'make' => new MakeHook(
                    command: $command,
                    timeout: $timeout,
                    factory: $factory,
                ),
                'symfony_console' => new SfConsoleHook(
                    command: $command,
                    timeout: $timeout,
                    factory: $factory,
                ),
                default => throw new DomainException("Error, hook of type {$type} is invalid"),
            };
        }

        return new HooksCollection($collections);
    },
];
