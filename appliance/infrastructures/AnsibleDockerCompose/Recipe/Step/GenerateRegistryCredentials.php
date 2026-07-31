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

namespace Teknoo\Space\Infrastructures\AnsibleDockerCompose\Recipe\Step;

use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\DockerComposeCluster;
use Teknoo\Space\Object\Config\Exception\UnsupportedClusterTypeException;

use function bin2hex;
use function hash;
use function password_hash;
use function random_bytes;

use const PASSWORD_BCRYPT;

/**
 * Mint the per-account private registry credentials (username = account namespace, random password, bcrypt
 * htpasswd line) and a dedicated container name, then stage both the credential fields consumed by
 * {@see \Teknoo\Space\Recipe\Step\AccountRegistry\PersistRegistryCredential} and the Ansible `extraVars` used by
 * the registry playbook. The registry is reachable only over the external private network by its container name,
 * mirroring the Kubernetes {@see \Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Registry\
 * CreateRegistryDeployment} but provisioned over SSH/Ansible on the remote Docker host.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class GenerateRegistryCredentials
{
    private const string CONTAINER_SUFFIX = '-registry';

    private const string CONFIG_SUFFIX = '-docker-config';

    private const string VOLUME_SUFFIX = '-registry-data';

    public function __construct(
        private readonly string $registryImage,
        private readonly string $registryNetwork,
        private readonly int|string $registryPort,
        private readonly bool $registryTls,
        private readonly string $deployRoot,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        ClusterCatalog $clusterCatalog,
        string $accountNamespace,
    ): self {
        $cluster = $clusterCatalog->getClusterForRegistry();

        if (!$cluster instanceof DockerComposeCluster) {
            throw new UnsupportedClusterTypeException('This step only supports docker-compose clusters');
        }

        $port = (int) $this->registryPort;

        $containerName = $accountNamespace . self::CONTAINER_SUFFIX;
        $volumeName = $accountNamespace . self::VOLUME_SUFFIX;
        $configName = $accountNamespace . self::CONFIG_SUFFIX;

        $username = $accountNamespace;
        $password = hash('sha256', bin2hex(random_bytes(32)) . $accountNamespace);
        $htpasswd = $username . ':' . password_hash($password, PASSWORD_BCRYPT);

        $registryUrl = $containerName . ':' . $port;

        $manager->updateWorkPlan([
            'registryUrl' => $registryUrl,
            'registryAccountName' => $username,
            'registryPassword' => $password,
            'registryConfigName' => $configName,
            'kubeNamespace' => $accountNamespace,
            'persistentVolumeClaimName' => $volumeName,
            'extraVars' => [
                'registry_container' => $containerName,
                'registry_image' => $this->registryImage,
                'registry_network' => $this->registryNetwork,
                'registry_port' => $port,
                'registry_tls' => $this->registryTls,
                'registry_htpasswd' => $htpasswd,
                'registry_volume' => $volumeName,
                'deploy_root' => $this->deployRoot,
            ],
        ]);

        return $this;
    }
}
