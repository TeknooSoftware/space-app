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
use function parse_url;
use function password_hash;
use function random_bytes;

use const PASSWORD_BCRYPT;

/**
 * Mint the per-account private registry credentials (username = account namespace, random password, bcrypt
 * htpasswd line) and a dedicated container name, then stage both the credential fields consumed by
 * {@see \Teknoo\Space\Recipe\Step\AccountRegistry\PersistRegistryCredential} and the Ansible `extraVars` used by
 * the registry playbook.
 *
 * The registry container lives on the external private network, but neither the worker (Buildah push) nor the
 * Docker host daemon (`docker compose up` pull) can resolve a container name: the registry is therefore exposed by
 * the host's Traefik on the `websecure` entrypoint under the per-account host name
 * `<namespace>-registry.<docker host>` (a DNS record for this name, or a wildcard on the host, must exist), which is
 * the `registryUrl` the projects push to and the Compose stack pulls from. The playbook then logs the deploy user
 * in on that registry so the pull is authenticated. This mirrors the Kubernetes
 * {@see \Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Registry\CreateRegistryDeployment} (registry behind
 * an Ingress) but provisioned over SSH/Ansible on the remote Docker host.
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
        private readonly string $traefikContainer = 'traefik',
        private readonly string $traefikDynamicDir = '/etc/traefik/dynamic',
        private readonly string $traefikEntrypointWebsecure = 'websecure',
        private readonly ?string $traefikDefaultCertresolver = null,
    ) {
    }

    /**
     * Host of the docker host management address (`ssh://user@host:port`), the registry FQDN is built on it.
     * Same rule as {@see BuildRegistryInventory}: the host is required, the user and the port are ignored.
     */
    private function extractHost(string $masterAddress): string
    {
        $parts = parse_url($masterAddress);
        $host = '';
        if (false !== $parts) {
            $host = (string)($parts['host'] ?? '');
        }

        if ('' === $host) {
            throw new UnsupportedClusterTypeException(
                "Invalid docker host address '{$masterAddress}': unable to parse the host to name the registry",
            );
        }

        return $host;
    }

    public function __invoke(
        ManagerInterface $manager,
        ClusterCatalog $clusterCatalog,
        string $accountNamespace,
        ?string $registryClusterName = null,
    ): self {
        //The registry cluster is resolved once per task by `SelectRegistryCluster`; falls back to the
        //first cluster supporting the registry when the task did not resolve it.
        $cluster = $clusterCatalog->getClusterForRegistry($registryClusterName);

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

        $registryHost = $containerName . '.' . $this->extractHost($cluster->masterAddress);
        //Bare host, no scheme and no port: `Image::getUrl()` prefixes the image names with it and Buildah logs in
        //on it, Traefik terminates the TLS on the websecure entrypoint.
        $registryUrl = $registryHost;

        $extraVars = [
            'registry_container' => $containerName,
            'registry_host' => $registryHost,
            'registry_image' => $this->registryImage,
            'registry_network' => $this->registryNetwork,
            'registry_port' => $port,
            'registry_tls' => $this->registryTls,
            'registry_account' => $username,
            'registry_password' => $password,
            'registry_htpasswd' => $htpasswd,
            'registry_volume' => $volumeName,
            'deploy_root' => $this->deployRoot,
            'traefik_container' => $this->traefikContainer,
            'traefik_dynamic_dir' => $this->traefikDynamicDir,
            'traefik_entrypoint_websecure' => $this->traefikEntrypointWebsecure,
        ];

        if (!empty($this->traefikDefaultCertresolver)) {
            $extraVars['traefik_default_certresolver'] = $this->traefikDefaultCertresolver;
        }

        $manager->updateWorkPlan([
            'registryUrl' => $registryUrl,
            'registryAccountName' => $username,
            'registryPassword' => $password,
            'registryConfigName' => $configName,
            'kubeNamespace' => $accountNamespace,
            'persistentVolumeClaimName' => $volumeName,
            'extraVars' => $extraVars,
        ]);

        return $this;
    }
}
