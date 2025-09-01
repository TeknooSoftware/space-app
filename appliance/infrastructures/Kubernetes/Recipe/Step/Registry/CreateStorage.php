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

namespace Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Registry;

use DateTimeInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Kubernetes\Model\PersistentVolumeClaim;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Object\Persisted\AccountRegistry;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CreateStorage
{
    private const string PVC_CLASS_SUFFIX = '-pvc';

    public function __construct(
        private readonly DatesService $datesService,
        private readonly bool $preferRealDate,
    ) {
    }

    private function createPersistentVolumeClaim(
        string $name,
        string $namespace,
        string $storageSize,
        string $storageProvisioner,
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
                'storageClassName' => $storageProvisioner,
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
        ClusterCatalog $clusterCatalog,
        ?AccountRegistry $accountRegistry = null,
    ): self {
        $clusterRegistry = $clusterCatalog->getClusterForRegistry();
        $client = $clusterRegistry->getKubernetesRegistryClient();
        $client->setNamespace($kubeNamespace);

        $pvcName = $accountRegistry?->getPersistentVolumeClaimName() ?? $accountNamespace . self::PVC_CLASS_SUFFIX;

        $persistentVolumeClaim = $this->createPersistentVolumeClaim(
            $pvcName,
            $kubeNamespace,
            $storageSizeToClaim,
            $clusterRegistry->storageProvisioner,
        );

        $persistentVolumeClaimRepository = $client->persistentVolumeClaims();
        if (!$persistentVolumeClaimRepository->exists((string) $persistentVolumeClaim->getMetadata('name'))) {
            $persistentVolumeClaimRepository->apply($persistentVolumeClaim);
        }

        $this->datesService->passMeTheDate(
            static function (DateTimeInterface $dateTime) use ($accountHistory, $pvcName): void {
                $accountHistory->addToHistory(
                    'teknoo.space.text.account.kubernetes.persistent_volume_claim',
                    $dateTime,
                    false,
                    [
                        'persistentVolumeClaim' => $pvcName
                    ],
                );
            },
            $this->preferRealDate,
        );

        $manager->updateWorkPlan(['persistentVolumeClaimName' => $pvcName]);

        return $this;
    }
}
