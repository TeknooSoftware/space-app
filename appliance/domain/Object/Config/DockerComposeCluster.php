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

namespace Teknoo\Space\Object\Config;

use Teknoo\East\Paas\Object\ClusterCredentials;

/**
 * Docker-compose cluster configuration. Carries the SSH connection data (key-only, rootless) needed to build
 * a deploy-time `ClusterCredentials`. Unlike {@see KubernetesCluster} it has no Kubernetes client, no storage
 * provisioner and never uses HNC. It does support a per-account OCI registry, provisioned over Ansible on the
 * remote Docker host (see the AnsibleDockerCompose registry-install plan).
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class DockerComposeCluster implements ConfigClusterInterface
{
    public readonly bool $useHnc;

    public function __construct(
        public readonly string $name,
        public readonly string $sluggyName,
        public readonly string $type,
        public readonly string $masterAddress,
        public readonly string $dashboardAddress,
        public readonly bool $isExternal,
        public readonly string $clientKey = '',
        public readonly string $username = '',
        public readonly string $caCertificate = '',
        public readonly bool $supportRegistry = true,
    ) {
        $this->useHnc = false;
    }

    /**
     * Build the deploy-time identity used by the East PaaS docker-compose driver. Key-only, rootless: the SSH
     * private key rides in `clientKey`, the known_hosts host key in `caCertificate`, no password is ever set.
     * When `username` is empty the SSH user is expected to be embedded in `masterAddress` (`ssh://user@host`).
     */
    public function getCredentials(): ClusterCredentials
    {
        return new ClusterCredentials(
            caCertificate: $this->caCertificate,
            clientKey: $this->clientKey,
            username: $this->username,
        );
    }
}
