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

namespace Teknoo\Space\Recipe\Step\Project;

use Teknoo\East\Paas\Contracts\Object\ImageRegistryInterface;
use Teknoo\East\Paas\Object\Cluster;
use Teknoo\East\Paas\Object\ClusterCredentials;
use Teknoo\East\Paas\Object\Environment;
use Teknoo\East\Paas\Object\ImageRegistry;
use Teknoo\East\Paas\Object\XRegistryAuth;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Object\Persisted\AccountRegistry;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class UpdateProjectCredentialsFromAccount
{
    public function __invoke(
        SpaceProject $spaceProject,
        AccountWallet $accountWallet,
        AccountRegistry $accountRegistry,
        ClusterCatalog $clusterCatalog,
    ): UpdateProjectCredentialsFromAccount {
        $eastProject = $spaceProject->project;
        $eastProject->visit(
            [
                'imagesRegistry' => static function (
                    ImageRegistryInterface $imageRegistry,
                ) use (
                    $eastProject,
                    $accountRegistry,
                ): void {
                    if (!$imageRegistry instanceof ImageRegistry) {
                        return;
                    }

                    $eastProject->setImagesRegistry(
                        new ImageRegistry(
                            $accountRegistry->getRegistryUrl(),
                            new XRegistryAuth(
                                username: $accountRegistry->getRegistryAccountName(),
                                password: $accountRegistry->getRegistryPassword(),
                                auth: $accountRegistry->getRegistryConfigName(),
                                serverAddress: $accountRegistry->getRegistryUrl(),
                            ),
                        ),
                    );
                },
                'clusters' => static function (iterable $clusters) use ($accountWallet, $clusterCatalog): void {
                    foreach ($clusters as $cluster) {
                        if ($cluster instanceof Cluster) {
                            $cluster->visit(
                                'environment',
                                static function (Environment $environment) use (
                                    $cluster,
                                    $accountWallet,
                                    $clusterCatalog,
                                ): void {
                                    $clusterName = (string) $cluster;
                                    if (!$accountWallet->has($clusterName, $environment)) {
                                        return;
                                    }

                                    $clusterConfig = $clusterCatalog->getCluster($clusterName);
                                    $cluster->setType($clusterConfig->type);
                                    $cluster->useHierarchicalNamespaces($clusterConfig->useHnc);
                                    $cluster->setAddress($clusterConfig->masterAddress);
                                    $cluster->setLocked(true);

                                    /** @var AccountEnvironment $accountEnvironment */
                                    $accountEnvironment = $accountWallet->get($clusterName, $environment);
                                    $cluster->setNamespace($accountEnvironment->getNamespace());
                                    $cluster->setIdentity(
                                        new ClusterCredentials(
                                            caCertificate: $accountEnvironment->getCaCertificate(),
                                            clientCertificate: $accountEnvironment->getClientCertificate(),
                                            clientKey: $accountEnvironment->getClientKey(),
                                            token: $accountEnvironment->getToken(),
                                        ),
                                    );
                                },
                            );
                        }
                    }
                }
            ]
        );

        return $this;
    }
}
