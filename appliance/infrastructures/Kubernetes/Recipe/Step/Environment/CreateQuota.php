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
use Teknoo\East\Paas\Object\Account;
use Teknoo\East\Paas\Object\AccountQuota;
use Teknoo\Kubernetes\Model\ResourceQuota;
use Teknoo\Space\Infrastructures\Kubernetes\Traits\InsertModelTrait;
use Teknoo\Space\Object\Config\Cluster as ClusterConfig;
use Teknoo\Space\Object\Persisted\AccountHistory;

use function in_array;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CreateQuota
{
    /**
     * @use InsertModelTrait<ResourceQuota>
     */
    use InsertModelTrait;

    private const QUOTA_SUFFIX = '-quota';

    public function __construct(
        private DatesService $datesService,
        private bool $preferRealDate,
    ) {
    }

    /**
     * @param AccountQuota[] $quotas
     */
    private function createResourceQuota(string $name, string $namespace, iterable $quotas): ResourceQuota
    {
        $hard = [];
        foreach ($quotas as $quota) {
            if (in_array($quota->type, ['cpu', 'memory', 'storage', 'ephemeral-storage'])) {
                $hard["requests.{$quota->type}"] = $quota->requires;

                if (in_array($quota->type, ['cpu', 'memory', 'ephemeral-storage'])) {
                    $hard["limits.{$quota->type}"] = $quota->capacity;
                }
            } else {
                $hard[$quota->type] = $quota->requires;
            }
        }

        return new ResourceQuota([
            'metadata' => [
                'name' => $name,
                'namespace' => $namespace,
                'labels' => [
                    'name' => $name,
                ],
            ],
            'spec' => [
                'hard' => $hard,
            ],
        ]);
    }

    public function __invoke(
        ManagerInterface $manager,
        string $kubeNamespace,
        string $accountNamespace,
        Account $accountInstance,
        AccountHistory $accountHistory,
        ClusterConfig $clusterConfig,
    ): self {
        $client = $clusterConfig->getKubernetesClient();

        $name = $accountNamespace . self::QUOTA_SUFFIX;

        $accountInstance->visit(
            'quotas',
            function (iterable $quotas) use ($manager, $name, $kubeNamespace, $accountHistory, $client): void {
                if (empty($quotas)) {
                    return;
                }

                $model = $this->createResourceQuota(
                    name: $name,
                    namespace: $kubeNamespace,
                    quotas: $quotas,
                );

                $client->setNamespace($kubeNamespace);
                $this->insertModel(
                    $client->resourceQuotas(),
                    $model,
                    true,
                );

                $this->datesService->passMeTheDate(
                    static function (DateTimeInterface $dateTime) use ($accountHistory, $name) {
                        $accountHistory->addToHistory(
                            'teknoo.space.text.account.kubernetes.quota',
                            $dateTime,
                            false,
                            [
                                'quota' => $name
                            ]
                        );
                    },
                    $this->preferRealDate,
                );

                $manager->updateWorkPlan([
                    'quotaName' => $name,
                ]);
            }
        );

        return $this;
    }
}
