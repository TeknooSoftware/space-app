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

namespace Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment;

use DateTimeInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\KubernetesCluster;
use Teknoo\Space\Object\Persisted\AccountHistory;

/**
 * Worker side of an environment removal (`DeleteEnvironmentsTask`): for each removed environment, deletes
 * its Kubernetes namespace when the cluster is a Kubernetes one and the namespace is labelled with the
 * account id (a namespace owned by another account, or already gone, is left untouched). Clusters of any
 * other type have nothing to tear down here: the step only records it in the account history, like
 * `SkipQuotaRefresh` does for quotas. The history is persisted by the task plan's `UpdateAccountHistory`.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class DeleteNamespaces
{
    public function __construct(
        private readonly DatesService $datesService,
        private readonly bool $preferRealDate,
    ) {
    }

    /**
     * @param array{envName: string, clusterName: string, namespace: string} $environment
     */
    private function addToHistory(AccountHistory $accountHistory, string $message, array $environment): void
    {
        $this->datesService->passMeTheDate(
            static function (DateTimeInterface $dateTime) use ($accountHistory, $message, $environment): void {
                $accountHistory->addToHistory(
                    $message,
                    $dateTime,
                    false,
                    $environment,
                );
            },
            $this->preferRealDate,
        );
    }

    /**
     * @param list<array{envName: string, clusterName: string, namespace: string}> $environmentsToDelete
     */
    public function __invoke(
        ClusterCatalog $clusterCatalog,
        array $environmentsToDelete,
        string $accountId,
        AccountHistory $accountHistory,
    ): self {
        foreach ($environmentsToDelete as $environment) {
            $clusterConfig = $clusterCatalog->getCluster($environment['clusterName']);
            if (!$clusterConfig instanceof KubernetesCluster) {
                $this->addToHistory(
                    $accountHistory,
                    'teknoo.space.text.account.environment.nothing_to_delete',
                    $environment,
                );

                continue;
            }

            $repository = $clusterConfig->getKubernetesClient()->namespaces();
            $nsModel = $repository->setLabelSelector(['name' => $environment['namespace']])->first();

            /** @var array{metadata?: array{labels?: array{id?: ?string}}} $modelArray */
            $modelArray = $nsModel?->toArray() ?? [];
            if (
                empty($nsModel)
                || empty($modelArray['metadata']['labels']['id'])
                || $modelArray['metadata']['labels']['id'] !== $accountId
            ) {
                $this->addToHistory(
                    $accountHistory,
                    'teknoo.space.text.account.kubernetes.namespace_not_found',
                    $environment,
                );

                continue;
            }

            $repository->delete($nsModel);

            $this->addToHistory(
                $accountHistory,
                'teknoo.space.text.account.kubernetes.namespace_deleted',
                $environment,
            );
        }

        return $this;
    }
}
