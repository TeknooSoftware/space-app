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

use ArrayObject;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mercure\ProtocolVersion;
use Teknoo\Space\Object\Config\SubscriptionPlan;
use Teknoo\Space\Object\Config\SubscriptionPlanCatalog;

use function array_map;
use function DI\env;
use function sys_get_temp_dir;

/*
 * MercureBundle turns the hub's protocol version into a `ProtocolVersion` enum case while the Symfony
 * container is compiled, so it can be neither a `%env()%` placeholder nor a `DI\env()` definition,
 * which produces one: the variable is read here, like the `SPACE_DC_*` ones of
 * di.variables.east.paas.php. An unknown value is rejected, with the list of the allowed ones, by the
 * enum node of the bundle when `config/packages/mercure.yaml` is processed.
 */
$mercureProtocolVersion = ProtocolVersion::tryFrom((string) ($_ENV['MERCURE_PROTOCOL_VERSION'] ?? ''));
if (empty($mercureProtocolVersion)) {
    $mercureProtocolVersion = ProtocolVersion::Legacy->value;
}

return [
    //App variables
    'teknoo.space.hostname' => env('SPACE_HOSTNAME', 'localhost'),
    'teknoo.space.job_root' => env('SPACE_JOB_ROOT', sys_get_temp_dir()),

    'teknoo.space.mercure.protocol_version' => $mercureProtocolVersion,

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
