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
use Teknoo\East\Paas\Object\Account;
use Teknoo\Kubernetes\Model\NamespaceModel;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Persisted\AccountHistory;

use function strtolower;
use function var_export;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CreateNamespace
{
    public function __construct(
        private string $rootNamespace,
        private string $registryRootNamespace,
        private DatesService $datesService,
        private bool $preferRealDate,
    ) {
    }

    private function createNamespaceModel(string $namespace, string $id): NamespaceModel
    {
        return new NamespaceModel([
            'metadata' => [
                'name' => $namespace,
                'labels' => [
                    'name' => $namespace,
                    'id' => $id,
                ],
            ]
        ]);
    }

    public function __invoke(
        ManagerInterface $manager,
        Account $accountInstance,
        AccountHistory $accountHistory,
        string $accountNamespace,
        ClusterCatalog $clusterCatalog,
        bool $forRegistry,
        ?string $clusterName = null,
        ?string $envName = null,
    ): self {
        if ($forRegistry) {
            $namespaceValue = $this->registryRootNamespace . $accountNamespace;
            $client = $clusterCatalog->getClusterForRegistry()->getKubernetesClient();
        } else {
            if (empty($clusterName)) {
                throw new \LogicException('Missing clusterName where create the namespace');
            }

            $namespaceValue = strtolower($this->rootNamespace . $accountNamespace . '-' . $envName);
            $clusterConfig = $clusterCatalog->getCluster($clusterName);
            $client = $clusterConfig->getKubernetesClient();
        }

        $accountId = $accountInstance->getId();
        $accountName = (string) $accountInstance;

        $repository = $client->namespaces();
        $model = $repository->setFieldSelector(['metadata.name' => $namespaceValue])->first();

        $modelArray = [];
        if ($model) {
            /** @var array{metadata: array{labels: array{id: ?string}}} $modelArray */
            $modelArray = $model->toArray();
        }

        if (
            !empty($modelArray)
            && (
                empty($modelArray['metadata']['labels']['id'])
                || $modelArray['metadata']['labels']['id'] !== $accountId
            )
        ) {
            throw new \DomainException(
                "Error the namespace `{$namespaceValue}` is not owned by the account {$accountName} ({$accountId})"
            );
        }

        $repository->apply($this->createNamespaceModel($namespaceValue, $accountId));

        //Update Account's history
        $this->datesService->passMeTheDate(
            static function (DateTimeInterface $dateTime) use ($accountHistory, $namespaceValue, $forRegistry) {
                $accountHistory->addToHistory(
                    'teknoo.space.text.account.kubernetes.namespace',
                    $dateTime,
                    false,
                    [
                        'namespace' => $namespaceValue,
                        'for-registry' => var_export($forRegistry, true),
                    ],
                );
            },
            $this->preferRealDate,
        );

        //Update workplan
        $manager->updateWorkPlan([
            'kubeNamespace' => $namespaceValue,
        ]);

        return $this;
    }
}
