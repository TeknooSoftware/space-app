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
use Teknoo\Kubernetes\Client as KubernetesClient;
use Teknoo\Kubernetes\Model\NamespaceModel;
use Teknoo\Space\Object\Persisted\AccountHistory;

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
        private KubernetesClient $client,
        private string $rootNamespace,
        private DatesService $datesService,
        private bool $prefereRealDate,
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
        Account $account,
    ): self {
        $namespaceRepository = $this->client->namespaces();

        $originalNS = $accountNamespace;
        $namespaceValue = $this->rootNamespace . $accountNamespace;
        $counter = 2;

        $accountId = $account->getId();
        do {
            $model = $namespaceRepository->setFieldSelector(['metadata.name' => $namespaceValue])->first();
            if (null === $model) {
                $namespace = $this->createNamespaceModel($namespaceValue, $accountId);
                $namespaceRepository->apply($namespace);

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
                $namespaceValue = $this->rootNamespace . $accountNamespace;
            }
        } while (true);

        $this->datesService->passMeTheDate(
            static function (DateTimeInterface $dateTime) use ($accountHistory, $namespaceValue) {
                $accountHistory->addToHistory(
                    'teknoo.space.text.account.kubernetes.namespace',
                    $dateTime,
                    false,
                    [
                        'namespace' => $namespaceValue
                    ]
                );
            },
            $this->prefereRealDate,
        );

        $this->client->setNamespace($namespaceValue);

        $account->setNamespace($accountNamespace);
        $account->setPrefixNamespace($this->rootNamespace);
        $this->writer->save($account);

        $manager->updateWorkPlan(['kubeNamespace' => $namespaceValue]);

        return $this;
    }
}
