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
use Teknoo\Kubernetes\Model\PersistentVolumeClaim;
use Teknoo\Space\Object\Config\Cluster as ClusterConfig;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\Persisted\AccountCredential;
use Teknoo\Space\Object\Persisted\AccountHistory;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CreateStorage
{
    private const PVC_CLASS_SUFFIX = '-pvc';

    public function __construct(
        private DatesService $datesService,
        private string $storageProvisioner,
        private bool $prefereRealDate,
    ) {
    }

    private function createPersistentVolumeClaim(
        string $name,
        string $namespace,
        string $storageSize
    ): PersistentVolumeClaim {
        return new PersistentVolumeClaim([
            'metadata' => [
                'name' => $name,
                'namespace' => $namespace,
                'labels' => [
                    'name' => $name,
                ],
            ],
            'spec' => [
                'accessModes' => [
                    'ReadWriteOnce'
                ],
                'storageClassName' => $this->storageProvisioner,
                'resources' => [
                    'requests' => [
                        'storage' => $storageSize
                    ]
                ],
            ],
        ]);
    }

    public function __invoke(
        ManagerInterface $manager,
        string $kubeNamespace,
        string $accountNamespace,
        AccountHistory $accountHistory,
        string $storageSizeToClaim,
        ClusterConfig $clusterConfig,
        ?AccountWallet $accountWallet = null,
    ): self {
        $client = $clusterConfig->kubernetesClient;

        $accountCredential = null;
        if ($accountWallet) {
            $accountCredential = $accountWallet[$clusterConfig->name];
        }
        $pvcName = $accountCredential?->getPersistentVolumeClaimName() ?? $accountNamespace . self::PVC_CLASS_SUFFIX;

        $persistentVolumeClaim = $this->createPersistentVolumeClaim(
            $pvcName,
            $kubeNamespace,
            $storageSizeToClaim,
        );

        $persistentVolumeClaimRepository = $client->persistentVolumeClaims();
        if (!$persistentVolumeClaimRepository->exists((string) $persistentVolumeClaim->getMetadata('name'))) {
            $persistentVolumeClaimRepository->apply($persistentVolumeClaim);
        }

        $this->datesService->passMeTheDate(
            static function (DateTimeInterface $dateTime) use ($accountHistory, $pvcName) {
                $accountHistory->addToHistory(
                    'teknoo.space.text.account.kubernetes.persistent_volume_claim',
                    $dateTime,
                    false,
                    [
                        'persistentVolumeClaim' => $pvcName
                    ]
                );
            },
            $this->prefereRealDate,
        );

        $manager->updateWorkPlan(['persistentVolumeClaimName' => $pvcName]);

        return $this;
    }
}
