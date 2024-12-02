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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Object\Config;

use RuntimeException;
use Teknoo\Kubernetes\Client;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Cluster
{
    private ?Client $kubernetesClient = null;
    private Client $kubernetesRegistryClient;

    /**
     * @var callable|null
     */
    private $clientInit = null;

    public function __construct(
        public readonly string $name,
        public readonly string $sluggyName,
        public readonly string $type,
        public readonly string $masterAddress,
        public readonly string $storageProvisioner,
        public readonly string $dashboardAddress,
        callable|Client $kubernetesClient,
        public readonly string $token,
        public readonly bool $supportRegistry,
        public readonly bool $useHnc,
    ) {
        if ($kubernetesClient instanceof Client) {
            $this->kubernetesClient = $kubernetesClient;
            $this->kubernetesRegistryClient = clone $kubernetesClient;
        } else {
            $this->clientInit = $kubernetesClient;
        }
    }

    public function getKubernetesClient(): Client
    {
        if (
            null === $this->kubernetesClient
            && null !== $this->clientInit
        ) {
            $this->kubernetesClient = ($this->clientInit)();
            if ($this->supportRegistry) {
                $this->kubernetesRegistryClient = clone $this->kubernetesClient;
            }
            $this->clientInit = null;
        }

        if (null === $this->kubernetesClient) {
            throw new RuntimeException("Error during kubernetes client's initializing");
        }

        return $this->kubernetesClient;
    }

    public function getKubernetesRegistryClient(): Client
    {
        if (!$this->supportRegistry) {
            throw new RuntimeException("Error this cluster does not support OCI registry");
        }

        if (null === $this->kubernetesClient) {
            $this->getKubernetesClient();
        }

        return $this->kubernetesRegistryClient;
    }
}
