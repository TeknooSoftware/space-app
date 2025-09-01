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

namespace Teknoo\Space\Recipe\Step\AccountCluster;

use DomainException;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Infrastructures\Kubernetes\Contracts\ClientFactoryInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Kubernetes\RepositoryRegistry;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Loader\AccountClusterLoader;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Persisted\AccountCluster;
use Teknoo\Space\Query\AccountCluster\LoadFromAccountQuery;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LoadAccountClusters
{
    public function __construct(
        private readonly AccountClusterLoader $loader,
        private readonly ClientFactoryInterface $clientFactory,
        private readonly RepositoryRegistry $repositoryRegistry,
    ) {
    }

    /**
     * @param iterable<AccountCluster> $clusters
     */
    private function generateCatalog(iterable $clusters, ClusterCatalog $initialClusterCatalog): ClusterCatalog
    {
        $formattedClusters = [];
        $aliases = [];

        /** @var AccountCluster $accountCluster */
        foreach ($clusters as $accountCluster) {
            $cluster = $accountCluster->convertToConfigCluster(
                $this->clientFactory,
                $this->repositoryRegistry,
            );

            $formattedClusters[$cluster->name] = $cluster;
            $aliases[$cluster->sluggyName] = $cluster->name;
        }

        if (empty($formattedClusters)) {
            return $initialClusterCatalog;
        }

        return new ClusterCatalog(
            $formattedClusters,
            $aliases,
            $initialClusterCatalog,
        );
    }

    public function __invoke(
        ManagerInterface $manager,
        ClusterCatalog $clusterCatalog,
        ?Account $accountInstance = null,
    ): self {
        if (null === $accountInstance || $clusterCatalog->hasParentCatalog()) {
            return $this;
        }

        /** @var Promise<iterable<AccountCluster>, mixed, mixed> $fetchedPromise */
        $fetchedPromise = new Promise(
            /** @var iterable<AccountCluster> $fetchedClusters */
            function (iterable $fetchedClusters) use ($manager, $clusterCatalog): void {
                /** @var iterable<AccountCluster> $fetchedClusters */
                $updatedClusterCatalog = $this->generateCatalog($fetchedClusters, $clusterCatalog);
                $manager->updateWorkPlan([
                    ClusterCatalog::class => $updatedClusterCatalog,
                    'clusterCatalog' => $updatedClusterCatalog,
                ]);
            },
            static fn (Throwable $error): ChefInterface => $manager->error(
                new DomainException(
                    message: 'teknoo.space.error.space_account.account_environment.fetching',
                    code: $error->getCode() > 0 ? $error->getCode() : 404,
                    previous: $error,
                )
            ),
        );

        $this->loader->query(
            new LoadFromAccountQuery($accountInstance),
            $fetchedPromise,
        );

        return $this;
    }
}
