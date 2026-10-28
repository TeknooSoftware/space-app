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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\AccountCluster;

use ArrayIterator;
use DomainException;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Loader\AccountClusterLoader;
use Teknoo\Space\Object\Persisted\AccountCluster;
use Teknoo\Space\Query\AccountCluster\LoadFromAccountQuery;
use Teknoo\Space\Recipe\Step\AccountCluster\InjectAccountClusters;

/**
 * Class InjectAccountClustersTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(InjectAccountClusters::class)]
class InjectAccountClustersTest extends TestCase
{
    private InjectAccountClusters $injectAccountClusters;

    private AccountClusterLoader&MockObject $loader;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = $this->createMock(AccountClusterLoader::class);
        $this->injectAccountClusters = new InjectAccountClusters(
            loader: $this->loader,
        );
    }

    public function testInvokeWithNullAccount(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');

        $parametersBag = $this->createMock(ParametersBag::class);
        $parametersBag->expects($this->never())->method('set');

        $this->loader->expects($this->never())->method('query');

        $this->assertInstanceOf(
            InjectAccountClusters::class,
            ($this->injectAccountClusters)(
                manager: $manager,
                parametersBag: $parametersBag,
                accountInstance: null,
            ),
        );
    }

    public function testInvokeWithClusters(): void
    {
        $firstCluster = $this->createStub(AccountCluster::class);
        $secondCluster = $this->createStub(AccountCluster::class);

        $this->loader->expects($this->once())
            ->method('query')
            ->with($this->isInstanceOf(LoadFromAccountQuery::class))
            ->willReturnCallback(function ($query, $promise) use ($firstCluster, $secondCluster) {
                $promise->success(new ArrayIterator([$firstCluster, $secondCluster]));
                return $this->loader;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');

        $parametersBag = $this->createMock(ParametersBag::class);
        $parametersBag->expects($this->once())
            ->method('set')
            ->with('accountClusters', [$firstCluster, $secondCluster]);

        $this->assertInstanceOf(
            InjectAccountClusters::class,
            ($this->injectAccountClusters)(
                manager: $manager,
                parametersBag: $parametersBag,
                accountInstance: $this->createStub(Account::class),
            ),
        );
    }

    public function testInvokeWithError(): void
    {
        $this->loader->expects($this->once())
            ->method('query')
            ->willReturnCallback(function ($query, $promise) {
                $promise->fail(new Exception('Test error', 500));
                return $this->loader;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($exception) {
                    return $exception instanceof DomainException
                        && 'teknoo.space.error.space_account.account_environment.fetching' === $exception->getMessage()
                        && 500 === $exception->getCode();
                })
            );

        $parametersBag = $this->createMock(ParametersBag::class);
        $parametersBag->expects($this->never())->method('set');

        $this->assertInstanceOf(
            InjectAccountClusters::class,
            ($this->injectAccountClusters)(
                manager: $manager,
                parametersBag: $parametersBag,
                accountInstance: $this->createStub(Account::class),
            ),
        );
    }

    public function testInvokeWithErrorCodeZero(): void
    {
        $this->loader->expects($this->once())
            ->method('query')
            ->willReturnCallback(function ($query, $promise) {
                $promise->fail(new Exception('Test error', 0));
                return $this->loader;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(function ($exception) {
                    return $exception instanceof DomainException
                        && 404 === $exception->getCode();
                })
            );

        $parametersBag = $this->createMock(ParametersBag::class);
        $parametersBag->expects($this->never())->method('set');

        $this->assertInstanceOf(
            InjectAccountClusters::class,
            ($this->injectAccountClusters)(
                manager: $manager,
                parametersBag: $parametersBag,
                accountInstance: $this->createStub(Account::class),
            ),
        );
    }
}
