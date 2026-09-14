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
];
