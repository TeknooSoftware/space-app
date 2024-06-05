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

namespace Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Environment;

use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Recipe\Step\AccountEnvironment\AbstractDeleteFromResumes;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class DeleteNamespaceFromResumes extends AbstractDeleteFromResumes
{
    public function __construct(
        private ClusterCatalog $clusterCatalog,
    ) {
    }

    protected function delete(AccountEnvironment $accountEnvironment): void
    {
        $clusterConfig = $this->clusterCatalog->getCluster($accountEnvironment->getClusterName());
        $client = $clusterConfig->getKubernetesClient();
        $namespace = $accountEnvironment->getNamespace();

        $repository = $client->namespaces();
        $nsModel = $repository->setLabelSelector(['name' => $namespace,])->first();

        if (empty($nsModel)) {
            return;
        }

        /** @var array{metadata: array{labels: array{id: ?string}}} $modelArray */
        $modelArray = $nsModel->toArray();

        if (
            empty($modelArray['metadata']['labels']['id'])
            || $modelArray['metadata']['labels']['id'] !== $accountEnvironment->getAccount()->getId()
        ) {
            return;
        }

        $repository->delete($nsModel);
    }
}
