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

use DateTimeInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Kubernetes\Client as KubernetesClient;
use Teknoo\Kubernetes\Model\ClusterRoleBinding;
use Teknoo\Kubernetes\Model\RoleBinding;
use Teknoo\Space\Object\Config\Cluster as ClusterConfig;
use Teknoo\Space\Object\Persisted\AccountHistory;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CreateRoleBinding
{
    private const ROLE_BINDING_SUFFIX = '-role-binding';
    private const CLUSTER_ROLE_BINDING_SUFFIX = '-cluster-role-binding';

    public function __construct(
        private DatesService $datesService,
        private bool $prefereRealDate,
    ) {
    }

    private function createRoleBinding(
        string $name,
        string $accountName,
        string $roleName,
        string $namespace
    ): RoleBinding {
        return new RoleBinding([
            'metadata' => [
                'name' => $name,
                'namespace' => $namespace,
                'labels' => [
                    'name' => $name,
                ],
            ],
            'subjects' => [
                [
                    'kind' => 'ServiceAccount',
                    'name' => $accountName,
                    'namespace' => $namespace,
                    'apiGroup' => ''
                ],
            ],
            'roleRef' => [
                'kind' => 'Role',
                'name' => $roleName,
                'namespace' => $namespace,
                'apiGroup' => 'rbac.authorization.k8s.io'
            ],
        ]);
    }

    private function createClusterRoleBinding(
        string $name,
        string $accountName,
        string $clusterRoleName,
        string $namespace
    ): ClusterRoleBinding {
        return new ClusterRoleBinding([
            'metadata' => [
                'name' => $name,
                'labels' => [
                    'name' => $name,
                ],
            ],
            'subjects' => [
                [
                    'kind' => 'ServiceAccount',
                    'name' => $accountName,
                    'namespace' => $namespace,
                    'apiGroup' => ''
                ],
            ],
            'roleRef' => [
                'kind' => 'ClusterRole',
                'name' => $clusterRoleName,
                'apiGroup' => 'rbac.authorization.k8s.io'
            ],
        ]);
    }

    public function __invoke(
        ManagerInterface $manager,
        string $kubeNamespace,
        string $accountNamespace,
        string $serviceName,
        string $roleName,
        string $clusterRoleName,
        AccountHistory $accountHistory,
        ClusterConfig $clusterConfig,
    ): self {
        $client = $clusterConfig->kubernetesClient;

        $roleBindingName = $accountNamespace . self::ROLE_BINDING_SUFFIX;
        $clusterRoleBindingName = $accountNamespace . self::CLUSTER_ROLE_BINDING_SUFFIX;

        $clusterRoleBinding = $this->createClusterRoleBinding(
            $clusterRoleBindingName,
            $serviceName,
            $clusterRoleName,
            $kubeNamespace
        );
        $clusterBindingRepository = $client->clusterRoleBindings();
        if (!$clusterBindingRepository->exists((string) $clusterRoleBinding->getMetadata('name'))) {
            $clusterBindingRepository->apply($clusterRoleBinding);
        }

        $client->setNamespace($kubeNamespace);

        $roleBinding = $this->createRoleBinding($roleBindingName, $serviceName, $roleName, $kubeNamespace);
        $bindingRepository = $client->roleBindings();
        if (!$bindingRepository->exists((string) $roleBinding->getMetadata('name'))) {
            $bindingRepository->apply($roleBinding);
        }

        $this->datesService->passMeTheDate(
            static function (DateTimeInterface $dateTime) use ($accountHistory, $roleBindingName) {
                $accountHistory->addToHistory(
                    'teknoo.space.text.account.kubernetes.role_binding',
                    $dateTime,
                    false,
                    [
                        'role_binding' => $roleBindingName
                    ]
                );
            },
            $this->prefereRealDate,
        );

        $manager->updateWorkPlan(['roleBindingName' => $roleBindingName]);

        return $this;
    }
}
