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

namespace App\Config;

use Psr\Container\ContainerInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ListObjectsAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Diactoros\MessageFactory;
use Teknoo\East\Foundation\Http\Message\MessageFactoryInterface;
use Teknoo\East\Foundation\Liveness\PingService;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Foundation\Time\TimerService;
use Teknoo\East\Paas\Infrastructures\Kubernetes\Contracts\ClientFactoryInterface;
use Teknoo\East\Paas\Object\ClusterCredentials;
use Teknoo\Kubernetes\Client as KubernetesClient;
use Teknoo\Kubernetes\RepositoryRegistry;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\AccessControl\ListObjectsAccessControl;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\AccessControl\ObjectAccessControl;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\User\LoadUserInSpace;
use Teknoo\Space\Liveness\PingFile;
use Teknoo\Space\Liveness\PingScheduler;
use Teknoo\Space\Middleware\HostnameRedirectionMiddleware;

use function DI\create;
use function DI\decorate;
use function DI\get;
use function base64_decode;

return [
    HostnameRedirectionMiddleware::class => static function (
        ContainerInterface $container
    ): HostnameRedirectionMiddleware {
        return new HostnameRedirectionMiddleware($container->get('teknoo.space.hostname'));
    },

    RecipeInterface::class => decorate(function ($previous, ContainerInterface $container): mixed {
        $previous = $previous->registerMiddleware(
            $container->get(HostnameRedirectionMiddleware::class),
            4
        );

        $previous = $previous->cook(
            $container->get(LoadUserInSpace::class),
            LoadUserInSpace::class,
            [],
            5
        );

        return $previous;
    }),

    ListObjectsAccessControlInterface::class => get(ListObjectsAccessControl::class),
    ListObjectsAccessControl::class => create(),

    ObjectAccessControlInterface::class => get(ObjectAccessControl::class),
    ObjectAccessControl::class => create(),

    KubernetesClient::class . ':create_account' => static function (ContainerInterface $container): KubernetesClient {
        $factory = $container->get(ClientFactoryInterface::class);

        $caCertificate = base64_decode($container->get('teknoo.space.kubernetes.create_account.ca_cert'));
        $credentials = new ClusterCredentials(
            token: $container->get('teknoo.space.kubernetes.create_account.token'),
            caCertificate: $caCertificate,
        );

        return $factory(
            $container->get('teknoo.space.kubernetes.master'),
            $credentials,
            $container->get(RepositoryRegistry::class)
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
];
