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

namespace Teknoo\Space\Recipe\Step\Project;

use DomainException;
use Teknoo\East\Paas\Contracts\Object\ImageRegistryInterface;
use Teknoo\East\Paas\Object\Cluster;
use Teknoo\East\Paas\Object\ClusterCredentials;
use Teknoo\East\Paas\Object\ImageRegistry;
use Teknoo\East\Paas\Object\XRegistryAuth;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\DTO\SpaceProject;
use Teknoo\Space\Object\Persisted\AccountCredential;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class UpdateProjectCredentialsFromAccount
{
    public function __construct(
        private ClusterCatalog $catalog,
    ) {
    }

    public function __invoke(
        SpaceProject $project,
        AccountWallet $accountWallet
    ): UpdateProjectCredentialsFromAccount {
        $accountCredential = null;
        foreach ($accountWallet as $accountCredential) {
            break;
        }

        if (empty($accountCredential)) {
            throw new DomainException("Error, no available credentials for this account");
        }

        $catalog = $this->catalog;
        $eastProject = $project->project;
        $eastProject->visit(
            [
                'imagesRegistry' => static function (
                    ImageRegistryInterface $imageRegistry,
                ) use (
                    $eastProject,
                    $accountCredential,
                ): void {
                    if (!$imageRegistry instanceof ImageRegistry) {
                        return;
                    }

                    $eastProject->setImagesRegistry(
                        new ImageRegistry(
                            $accountCredential->getRegistryUrl(),
                            new XRegistryAuth(
                                username: $accountCredential->getRegistryAccountName(),
                                password: $accountCredential->getRegistryPassword(),
                                auth: $accountCredential->getRegistryConfigName(),
                                serverAddress: $accountCredential->getRegistryUrl(),
                            ),
                        ),
                    );
                },
                'clusters' => static function (iterable $clusters) use ($accountWallet, $catalog): void {
                    foreach ($clusters as $cluster) {
                        if (!$cluster instanceof Cluster) {
                            continue;
                        }
                        $cluster->visit(
                            [
                                'name' => static function ($name) use ($cluster, $accountWallet, $catalog): void {
                                    if (!isset($accountWallet[$name])) {
                                        return;
                                    }

                                    $clusterConfig = $catalog->getCluster($name);
                                    $cluster->setType($clusterConfig->type);
                                    $cluster->setAddress($clusterConfig->masterAddress);
                                    $cluster->setLocked(true);

                                    $accountCredential = $accountWallet[$name];
                                    $cluster->setIdentity(
                                        new ClusterCredentials(
                                            caCertificate: $accountCredential->getCaCertificate(),
                                            clientCertificate: $accountCredential->getClientCertificate(),
                                            clientKey: $accountCredential->getClientKey(),
                                            token: $accountCredential->getToken(),
                                        ),
                                    );
                                }
                            ]
                        );
                    }
                }
            ]
        );

        return $this;
    }
}
