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

use Teknoo\East\Paas\Contracts\Object\ImageRegistryInterface;
use Teknoo\East\Paas\Object\Cluster;
use Teknoo\East\Paas\Object\ClusterCredentials;
use Teknoo\East\Paas\Object\ImageRegistry;
use Teknoo\East\Paas\Object\XRegistryAuth;
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
        private string $defaultClusterName,
        private string $defaultClusterType,
        private string $defaultClusterAddress,
    ) {
    }

    public function __invoke(
        SpaceProject $project,
        //todo Use AccountsCredentialsWallet
        AccountCredential $accountCredential,
    ): UpdateProjectCredentialsFromAccount {
        $eastProject = $project->project;
        $eastProject->visit(
            [
                'imagesRegistry' => function (
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
                            ),
                        ),
                    );
                },
                'clusters' => function (iterable $clusters) use ($accountCredential): void {
                    foreach ($clusters as $cluster) {
                        if (!$cluster instanceof Cluster) {
                            continue;
                        }

                        $cluster->visit(
                            [
                                'name' => function ($name) use ($cluster, $accountCredential): void {
                                    if ($this->defaultClusterName !== $name) {
                                        return;
                                    }

                                    $cluster->setType($this->defaultClusterType);
                                    $cluster->setAddress($this->defaultClusterAddress);
                                    $cluster->setLocked(true);
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
