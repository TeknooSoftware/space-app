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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\App\Config;

use ArrayObject;
use Psr\Container\ContainerInterface;
use Teknoo\Space\Object\Config\SubscriptionPlan;
use Teknoo\Space\Object\Config\SubscriptionPlanCatalog;

use function array_map;
use function DI\env;
use function sys_get_temp_dir;

return [
    //App variables
    'teknoo.space.hostname' => env('SPACE_HOSTNAME', 'localhost'),
    'teknoo.space.job_root' => env('SPACE_JOB_ROOT', sys_get_temp_dir()),

    'teknoo.space.subscription_plan_catalog' => static function (
        ContainerInterface $container
    ): SubscriptionPlanCatalog {
        static $catalog = null;
        if (null !== $catalog) {
            return $catalog;
        }

        $definitions = [];
        if ($container->has('teknoo.space.subscription_plan_catalog.definitions')) {
            $definitions = $container->get('teknoo.space.subscription_plan_catalog.definitions');

            if ($definitions instanceof ArrayObject) {
                $definitions = $definitions->getArrayCopy();
            }
        }

        $list = [];
        foreach ($definitions as $definition) {
            $plan = new SubscriptionPlan(...$definition);
            $list[$plan->id] = $plan;
        }

        return new SubscriptionPlanCatalog($list);
    },
];
