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

namespace Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Project;

use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Cluster;
use Teknoo\East\Paas\Object\ClusterCredentials;
use Teknoo\East\Paas\Object\Environment;
use Teknoo\East\Paas\Object\GitRepository;
use Teknoo\East\Paas\Object\ImageRegistry;
use Teknoo\East\Paas\Object\Project;
use Teknoo\East\Paas\Object\SshIdentity;
use Teknoo\East\Paas\Object\XRegistryAuth;
use Teknoo\Space\Object\Persisted\AccountCredential;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class PrepareProject
{
    public function __construct(
        private string $defaultClusterName,
        private string $defaultClusterType,
        private string $defaultClusterAddress,
        private string $defaultClusterEnv,
    ) {
    }

    public function __invoke(ManagerInterface $manager, Project $projectInstance, AccountCredential $credential): self
    {
        $projectInstance->setImagesRegistry(
            new ImageRegistry(
                $credential->getRegistryUrl(),
                new XRegistryAuth(
                    $credential->getRegistryAccountName(),
                    $credential->getRegistryPassword(),
                    '',
                    '',
                    $credential->getRegistryUrl()
                )
            )
        );

        $projectInstance->setSourceRepository(
            new GitRepository(
                '',
                'master',
                new SshIdentity('git', '')
            )
        );

        $cluster = new Cluster();
        $cluster->setName($this->defaultClusterName);
        $cluster->setType($this->defaultClusterType);
        $cluster->setAddress($this->defaultClusterAddress);
        $cluster->setEnvironment(new Environment($this->defaultClusterEnv));
        $cluster->setLocked(true);
        $cluster->setIdentity(
            new ClusterCredentials(
                caCertificate: $credential->getCaCertificate(),
                clientCertificate: $credential->getClientCertificate(),
                clientKey: $credential->getClientKey(),
                token: $credential->getToken()
            )
        );

        $projectInstance->setClusters([$cluster]);

        return $this;
    }
}
