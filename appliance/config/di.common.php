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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ListObjectsAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Diactoros\MessageFactory;
use Teknoo\East\Foundation\Http\Message\MessageFactoryInterface;
use Teknoo\East\Foundation\Liveness\PingService;
use Teknoo\East\Foundation\Recipe\PlanInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Foundation\Time\TimerService;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\AccessControl\ListObjectsAccessControl;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\AccessControl\ObjectAccessControl;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\User\LoadUserInSpace;
use Teknoo\Space\Liveness\PingFile;
use Teknoo\Space\Liveness\PingScheduler;
use Teknoo\Space\Middleware\HostnameRedirectionMiddleware;

use function DI\create;
use function DI\decorate;
use function DI\get;

return [
    HostnameRedirectionMiddleware::class => static function (
        ContainerInterface $container
    ): HostnameRedirectionMiddleware {
        return new HostnameRedirectionMiddleware($container->get('teknoo.space.hostname'));
    },

    PlanInterface::class => decorate(
        static function (
            PlanInterface $previous,
            ContainerInterface $container,
        ): PlanInterface {
            $previous->add(
                $container->get(HostnameRedirectionMiddleware::class)->execute(...),
                4
            );

            $previous->add(
                $container->get(LoadUserInSpace::class),
                5
            );

            return $previous;
        }
    ),

    ListObjectsAccessControlInterface::class => get(ListObjectsAccessControl::class),
    ListObjectsAccessControl::class => static function (ContainerInterface $container): ListObjectsAccessControl {
        return new ListObjectsAccessControl(
            $container->get(AuthorizationCheckerInterface::class),
            $container->get(TokenStorageInterface::class),
        );
    },

    ObjectAccessControlInterface::class => get(ObjectAccessControl::class),
    ObjectAccessControl::class => static function (ContainerInterface $container): ObjectAccessControl {
        return new ObjectAccessControl(
            $container->get(AuthorizationCheckerInterface::class),
            $container->get(TokenStorageInterface::class),
        );
    },

    MessageFactoryInterface::class => get(MessageFactory::class),

    PingFile::class => create()
        ->constructor(
            get(DatesService::class),
            get('teknoo.space.ping_file'),
        ),

    PingScheduler::class => static function (ContainerInterface $container): PingScheduler {
        return new PingScheduler(
            $container->get(PingService::class),
            $container->get(PingFile::class),
            $container->get(TimerService::class),
            (int) $container->get('teknoo.space.ping_seconds'),
        );
    },

    'teknoo.east.common.assets.destination.css.path' => 'public/build/css',
    'teknoo.east.common.assets.source.css.path' => 'public/',
    'teknoo.east.common.assets.destination.js.path' => 'public/build/js',
    'teknoo.east.common.assets.source.js.path' => 'public/',
    'teknoo.east.common.assets.final_location' => 'build/',

    'teknoo.east.common.assets.sets.css' => [
        'default' => [
            'css/bootstrap.min.css',
            'libs/@mdi/font/css/materialdesignicons.min.css',
            'css/icons.min.css',
            'libs/@iconscout/unicons/css/line.css',
            'css/style.min.css',
            'css/colors/default.css',
            'css/space.css',
        ],
    ],

    'teknoo.east.common.assets.sets.js' => [
        'default' => [
            'libs/bootstrap/js/bootstrap.bundle.min.js',
            'libs/feather-icons/feather.min.js',
            'libs/simplebar/simplebar.min.js',
            'js/app.js',
            'js/space.js',
        ],
    ],
];
