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

namespace Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Account;

use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Contracts\Object\Account\AccountAwareInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Kubernetes\Client as KubernetesClient;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\Persisted\AccountRegistry;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ReloadNamespace
{
    public function __construct(
        private readonly ClusterCatalog $catalog,
        private string $registryRootNamespace,
    ) {
    }

    public function __invoke(
        ManagerInterface $manager,
        Account $account,
        AccountWallet $accountWallet,
        ?AccountRegistry $accountRegistry = null,
    ): self {
        foreach ($accountWallet as $credential) {
            $clusterConfig = $this->catalog->getCluster($credential->getClusterName());

            $account->requireAccountNamespace(
                new class (
                    $manager,
                    $clusterConfig->getKubernetesClient(),
                    $accountRegistry,
                    $this->registryRootNamespace,
                ) implements AccountAwareInterface {
                    public function __construct(
                        public ManagerInterface $manager,
                        private KubernetesClient $client,
                        private ?AccountRegistry $accountRegistry,
                        private string $registryRootNamespace,
                    ) {
                    }

                    public function passAccountNamespace(
                        Account $account,
                        ?string $name,
                        ?string $namespace,
                        ?string $prefixNamespace,
                        bool $useHierarchicalNamespaces,
                    ): AccountAwareInterface {
                        $kubeNamespace = $prefixNamespace . $namespace;
                        $this->client->setNamespace($kubeNamespace);

                        $defaultRN = $this->registryRootNamespace . $namespace;
                        $registryNamespace = $this->accountRegistry?->getRegistryNamespace() ?? $defaultRN;

                        $this->manager->updateWorkPlan([
                            'accountNamespace' => $namespace,
                            'kubeNamespace' => $kubeNamespace,
                            'registryNamespace' => $registryNamespace,
                        ]);

                        return $this;
                    }
                }
            );
        }

        return $this;
    }
}
