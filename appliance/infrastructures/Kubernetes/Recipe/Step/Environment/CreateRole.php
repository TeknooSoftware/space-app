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

namespace Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment;

use DateTimeInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Kubernetes\Model\ClusterRole;
use Teknoo\Kubernetes\Model\Role;
use Teknoo\Space\Object\Config\Cluster as ClusterConfig;
use Teknoo\Space\Object\Persisted\AccountHistory;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CreateRole
{
    private const ROLE_SUFFIX = '-role';
    private const CLUSTER_ROLE_SUFFIX = '-cluster-role';

    public function __construct(
        private DatesService $datesService,
        private bool $preferRealDate,
    ) {
    }

    private function createRole(string $name, string $namespace): Role
    {
        return new Role([
            'metadata' => [
                'name' => $name,
                'namespace' => $namespace,
                'labels' => [
                    'name' => $name,
                ],
            ],
            'rules' => [
                [
                    'apiGroups' => [''],
                    'resources' => [
                        'pods',
                        'pods/log',
                        'pods/exec',
                        'services',
                        'secrets',
                        'replicationcontrollers',
                        'persistentvolumeclaims',
                        'configmaps',
                    ],
                    'verbs' => ['get', 'watch', 'list', 'create', 'update', 'patch', 'delete', 'deletecollection'],
                ],
                [
                    'apiGroups' => ['apps'],
                    'resources' => ['deployments', 'replicasets', 'statefulsets'],
                    'verbs' => ['get', 'watch', 'list', 'create', 'update', 'patch', 'delete', 'deletecollection'],
                ],
                [
                    'apiGroups' => ['batch'],
                    'resources' => ['jobs', 'cronjobs'],
                    'verbs' => ['get', 'watch', 'list', 'create', 'update', 'patch', 'delete', 'deletecollection'],
                ],
                [
                    'apiGroups' => ['networking.k8s.io'],
                    'resources' => ['ingresses'],
                    'verbs' => ['get', 'watch', 'list', 'create', 'update', 'patch', 'delete', 'deletecollection'],
                ],
            ],
        ]);
    }

    private function createClusterRole(string $name): ClusterRole
    {
        return new ClusterRole([
            'metadata' => [
                'name' => $name,
                'labels' => [
                    'name' => $name,
                ],
            ],
            'rules' => [
                [
                    'apiGroups' => [''],
                    'resources' => ['namespaces'],
                    'verbs' => ['get', 'watch'],
                ],
            ],
        ]);
    }

    public function __invoke(
        ManagerInterface $manager,
        string $kubeNamespace,
        string $accountNamespace,
        AccountHistory $accountHistory,
        ClusterConfig $clusterConfig,
    ): self {
        $client = $clusterConfig->getKubernetesClient();

        $roleName = $accountNamespace . self::ROLE_SUFFIX;
        $clusterRoleName = $accountNamespace . self::CLUSTER_ROLE_SUFFIX;

        $role = $this->createRole($roleName, $kubeNamespace);
        $roleRepository = $client->roles();
        $roleRepository->apply($role);

        $clusterRole = $this->createClusterRole($clusterRoleName);
        $clusterRoleRepository = $client->clusterRoles();
        $clusterRoleRepository->apply($clusterRole);

        $this->datesService->passMeTheDate(
            static function (DateTimeInterface $dateTime) use ($accountHistory, $roleName) {
                $accountHistory->addToHistory(
                    'teknoo.space.text.account.kubernetes.role',
                    $dateTime,
                    false,
                    [
                        'role' => $roleName
                    ]
                );
            },
            $this->preferRealDate,
        );

        $manager->updateWorkPlan([
            'roleName' => $roleName,
            'clusterRoleName' => $clusterRoleName,
        ]);

        return $this;
    }
}
