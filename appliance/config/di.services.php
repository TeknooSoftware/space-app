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

namespace Teknoo\Space\App\Config;

use Psr\Container\ContainerInterface;
use Teknoo\East\Paas\Contracts\Recipe\Plan\NewJobInterface;
use Teknoo\Space\Object\DTO\NewJob as NewJobDto;
use Teknoo\Space\Object\DTO\Task\DeleteEnvironmentsTask;
use Teknoo\Space\Object\DTO\Task\InstallEnvironmentTask;
use Teknoo\Space\Object\DTO\Task\InstallRegistryTask;
use Teknoo\Space\Object\DTO\Task\RefreshQuotaTask;
use Teknoo\Space\Object\DTO\Task\ReinstallEnvironmentTask;
use Teknoo\Space\Object\DTO\Task\ReinstallRegistryTask;
use Teknoo\Space\Service\NewTaskRecipeRegistry;
use Teknoo\Space\Service\WorkerTimeoutsChecker;

use function is_string;

return [
    NewTaskRecipeRegistry::class => static function (
        ContainerInterface $container,
    ): NewTaskRecipeRegistry {
        return (new NewTaskRecipeRegistry())
            ->register(NewJobDto::class, $container->get(NewJobInterface::class))
            ->register(InstallRegistryTask::class, $container->get('teknoo.space.task.plan.registry_install'))
            ->register(ReinstallRegistryTask::class, $container->get('teknoo.space.task.plan.registry_reinstall'))
            ->register(RefreshQuotaTask::class, $container->get('teknoo.space.task.plan.refresh_quota'))
            ->register(InstallEnvironmentTask::class, $container->get('teknoo.space.task.plan.environment_install'))
            ->register(
                ReinstallEnvironmentTask::class,
                $container->get('teknoo.space.task.plan.environment_reinstall'),
            )
            ->register(
                DeleteEnvironmentsTask::class,
                $container->get('teknoo.space.task.plan.environments_delete'),
            );
    },

    WorkerTimeoutsChecker::class => static function (ContainerInterface $container): WorkerTimeoutsChecker {
        //Timeouts indexed by their env var name, a missing parameter means no timeout
        $timeouts = [];
        foreach (
            [
                'SPACE_GIT_TIMEOUT' => 'teknoo.east.paas.git.cloning.timeout',
                'SPACE_IMG_BUILDER_TIMEOUT' => 'teknoo.east.paas.img_builder.build.timeout',
            ] as $envName => $parameterName
        ) {
            $timeouts[$envName] = null;
            if ($container->has($parameterName)) {
                $timeouts[$envName] = (float) $container->get($parameterName);
            }
        }

        $deploymentTimeouts = [];
        foreach (
            [
                'SPACE_DC_TIMEOUT' => 'teknoo.east.paas.docker-compose.timeout',
                'SPACE_KUBERNETES_CLIENT_TIMEOUT' => 'teknoo.east.paas.kubernetes.timeout',
            ] as $envName => $parameterName
        ) {
            $deploymentTimeouts[$envName] = null;
            if ($container->has($parameterName)) {
                $deploymentTimeouts[$envName] = (float) $container->get($parameterName);
            }
        }

        //Invalid definitions are ignored here, they are already rejected by the hooks collection
        $hooksTimeouts = [];
        if ($container->has('teknoo.space.hooks_collection.definitions')) {
            $defaultTimeout = (float) $container->get('teknoo.space.hooks_collection.default_timeout');
            foreach ($container->get('teknoo.space.hooks_collection.definitions') as $definition) {
                if (empty($definition['name']) || !is_string($definition['name'])) {
                    continue;
                }

                $hooksTimeouts[$definition['name']] = (float) ($definition['timeout'] ?? $defaultTimeout);
            }
        }

        return new WorkerTimeoutsChecker(
            timeLimit: ($container->get('teknoo.east.paas.di.worker.time_limit'))($container),
            timeouts: $timeouts,
            deploymentTimeouts: $deploymentTimeouts,
            hooksTimeouts: $hooksTimeouts,
        );
    },
];
