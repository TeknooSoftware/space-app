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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\AccountCluster;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Infrastructures\Kubernetes\Contracts\ClientFactoryInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Kubernetes\RepositoryRegistry;
use Teknoo\Space\Loader\AccountClusterLoader;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Recipe\Step\AccountCluster\LoadAccountClusters;

/**
 * Class loadAccountClustersTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(LoadAccountClusters::class)]
class LoadAccountClustersTest extends TestCase
{
    private LoadAccountClusters $loadAccountClusters;

    private AccountClusterLoader|MockObject $loader;

    private readonly ClientFactoryInterface|MockObject $clientFactory;

    private readonly RepositoryRegistry|MockObject $repositoryRegistry;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = $this->createMock(AccountClusterLoader::class);
        $this->clientFactory = $this->createMock(ClientFactoryInterface::class);
        $this->repositoryRegistry = $this->createMock(RepositoryRegistry::class);
        $this->loadAccountClusters = new LoadAccountClusters(
            loader: $this->loader,
            clientFactory: $this->clientFactory,
            repositoryRegistry: $this->repositoryRegistry,
        );
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            LoadAccountClusters::class,
            ($this->loadAccountClusters)(
                $this->createMock(ManagerInterface::class),
                $this->createMock(ClusterCatalog::class),
                $this->createMock(Account::class),
            ),
        );
    }
}
