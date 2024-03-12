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
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Kubernetes\Model\NamespaceModel;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Persisted\AccountHistory;

use function array_values;
use function count;
use function iterator_to_array;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CreateNamespace
{
    /**
     * @param WriterInterface<Account> $writer
     */
    public function __construct(
        private string $rootNamespace,
        private string $registryRootNamespace,
        private DatesService $datesService,
        private bool $preferRealDate,
        private WriterInterface $writer,
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
        string $accountNamespace,
        AccountHistory $accountHistory,
        Account $accountInstance,
        ClusterCatalog $clusterCatalog,
    ): self {
        $originalNS = $accountNamespace;
        $namespaceValue = $this->rootNamespace . $accountNamespace;
        $registryNamespaceValue = $this->registryRootNamespace . $accountNamespace;
        $counter = 2;

        $accountId = $accountInstance->getId();

        $catalogArray = array_values(iterator_to_array($clusterCatalog));
        $catalogArrayCount = count($catalogArray);
        $namespaceRepositoryList = [];

        for ($i = 0; $i < $catalogArrayCount; $i++) {
            $mustReset = false;
            $namespaceRepositoryList[$i] ??= $catalogArray[$i]->getKubernetesClient()->namespaces();

            do {
                $model = $namespaceRepositoryList[$i]->setFieldSelector(['metadata.name' => $namespaceValue])->first();
                if (null === $model) {
                    break;
                } else {
                    /** @var array{metadata: array{labels: array{id: ?string}}} $arr */
                    $arr = $model->toArray();

                    if (
                        isset($arr['metadata']['labels']['id'])
                        && $arr['metadata']['labels']['id'] === $accountId
                    ) {
                        break;
                    }

                    $accountNamespace = $originalNS . '-' . ($counter++);
                    $mustReset = true;
                    $namespaceValue = $this->rootNamespace . $accountNamespace;
                    $registryNamespaceValue = $this->registryRootNamespace . $accountNamespace;
                }
            } while (true);

            if ($mustReset) {
                $i = 0;
            }
        }

        //Create registry namespace
        $clusterForRegistry = $clusterCatalog->getClusterForRegistry();
        $registryClient = $clusterForRegistry->getKubernetesRegistryClient();
        $ns = $registryClient->namespaces();
        $ns->apply($this->createNamespaceModel($registryNamespaceValue, $accountId));

        //Apply model
        $namespaceModel = $this->createNamespaceModel($namespaceValue, $accountId);
        for ($i = 0; $i < $catalogArrayCount; $i++) {
            $client = $catalogArray[$i]->getKubernetesClient();
            $namespaceRepository = $client->namespaces();
            $namespaceRepository->apply($namespaceModel);

            $client->setNamespace($namespaceValue);
        }

        //Update Account's history
        $this->datesService->passMeTheDate(
            static function (DateTimeInterface $dateTime) use ($accountHistory, $namespaceValue) {
                $accountHistory->addToHistory(
                    'teknoo.space.text.account.kubernetes.namespace',
                    $dateTime,
                    false,
                    ['namespace' => $namespaceValue],
                );
            },
            $this->preferRealDate,
        );

        //Update Account instance
        $accountInstance->setNamespace($accountNamespace);
        $accountInstance->setPrefixNamespace($this->rootNamespace);
        $this->writer->save($accountInstance);

        //Update workplan
        $manager->updateWorkPlan([
            'kubeNamespace' => $namespaceValue,
            'registryNamespace' => $registryNamespaceValue,
        ]);

        return $this;
    }
}
