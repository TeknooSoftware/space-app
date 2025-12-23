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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Misc;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Job;
use Teknoo\Kubernetes\Client;
use Teknoo\Space\Object\Config\Cluster as ClusterConfig;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Recipe\Step\Job\ExtractProject;
use Teknoo\Space\Recipe\Step\Misc\ClusterAndEnvSelection;

/**
 * Class ExtractProjectTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ClusterAndEnvSelection::class)]
class ClusterAndEnvSelectionTest extends TestCase
{
    private ClusterAndEnvSelection $clusterAndEnvSelection;

    private ClusterCatalog $clusterCatalog;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();


        $clusterConfig = new ClusterConfig(
            name: 'foo',
            sluggyName: 'foo',
            type: 'foo',
            masterAddress: 'foo',
            storageProvisioner: 'foo',
            dashboardAddress: 'foo',
            kubernetesClient: $this->createStub(Client::class),
            token: 'foo',
            supportRegistry: true,
            useHnc: false,
            isExternal: false,
        );

        $this->clusterCatalog = new ClusterCatalog(
            ['clusterName' => $clusterConfig],
            ['cluster-name' => 'clusterName'],
        );

        $this->clusterAndEnvSelection = new ClusterAndEnvSelection($this->clusterCatalog);
    }

    public function testInvokeWithoutAccount(): void
    {
        $this->assertInstanceOf(
            ClusterAndEnvSelection::class,
            ($this->clusterAndEnvSelection)(
                $this->createStub(ManagerInterface::class),
                $this->createStub(ServerRequestInterface::class),
                $this->createStub(ParametersBag::class),
            )
        );
    }

    public function testInvokeWithAccount(): void
    {
        $this->assertInstanceOf(
            ClusterAndEnvSelection::class,
            ($this->clusterAndEnvSelection)(
                $this->createStub(ManagerInterface::class),
                $this->createStub(ServerRequestInterface::class),
                $this->createStub(ParametersBag::class),
                $this->createStub(AccountWallet::class),
            )
        );
    }

    public function testInvokeWithoutQueryParam(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getQueryParams')
            ->willReturn([]);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan['clusterName'])
                        && isset($workplan['envName'])
                        && '_all' === $workplan['envName'];
                })
            );

        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->exactly(5))
            ->method('set');

        $result = ($this->clusterAndEnvSelection)(
            $manager,
            $request,
            $bag,
        );

        $this->assertInstanceOf(ClusterAndEnvSelection::class, $result);
    }

    public function testInvokeWithClusterQueryParamWithoutEnv(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getQueryParams')
            ->willReturn(['cluster' => 'foo']);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan['clusterName'])
                        && 'foo' === $workplan['clusterName']
                        && isset($workplan['envName'])
                        && '_all' === $workplan['envName'];
                })
            );

        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->exactly(5))
            ->method('set')
            ->willReturnCallback(function ($key, $value) use ($bag) {
                if ('envName' === $key) {
                    $this->assertSame('_all', $value);
                } elseif ('namespace' === $key) {
                    $this->assertSame('_all', $value);
                }
                return $bag;
            });

        $result = ($this->clusterAndEnvSelection)(
            $manager,
            $request,
            $bag,
        );

        $this->assertInstanceOf(ClusterAndEnvSelection::class, $result);
    }

    public function testInvokeWithClusterQueryParamWithEnv(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getQueryParams')
            ->willReturn(['cluster' => 'foo']);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan['clusterName'])
                        && 'foo' === $workplan['clusterName']
                        && isset($workplan['envName'])
                        && '_all' === $workplan['envName'];
                })
            );

        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->exactly(5))
            ->method('set')
            ->willReturnCallback(function ($key, $value) use ($bag) {
                if ('envName' === $key) {
                    $this->assertSame('_all', $value);
                }
                return $bag;
            });

        $result = ($this->clusterAndEnvSelection)(
            $manager,
            $request,
            $bag,
        );

        $this->assertInstanceOf(ClusterAndEnvSelection::class, $result);
    }

    public function testInvokeWithAccountWalletAndMatchingCluster(): void
    {
        $accountEnv = $this->createMock(AccountEnvironment::class);
        $accountEnv
            ->method('getClusterName')
            ->willReturn('clusterName');
        $accountEnv
            ->method('getEnvName')
            ->willReturn('staging');
        $accountEnv->expects($this->once())
            ->method('getNamespace')
            ->willReturn('my-namespace');

        $accountWallet = $this->createMock(AccountWallet::class);
        $accountWallet->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$accountEnv]));

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getQueryParams')
            ->willReturn(['cluster' => 'clusterName~staging']);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan['clusterName'])
                        && 'foo' === $workplan['clusterName']
                        && isset($workplan['envName'])
                        && 'staging' === $workplan['envName'];
                })
            );

        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->exactly(5))
            ->method('set')
            ->willReturnCallback(function ($key, $value) use ($bag) {
                if ('namespace' === $key) {
                    $this->assertSame('my-namespace', $value);
                } elseif ('envName' === $key) {
                    $this->assertSame('staging', $value);
                }
                return $bag;
            });

        $result = ($this->clusterAndEnvSelection)(
            $manager,
            $request,
            $bag,
            $accountWallet,
        );

        $this->assertInstanceOf(ClusterAndEnvSelection::class, $result);
    }

    public function testInvokeWithAccountWalletWithoutMatchingCluster(): void
    {
        $accountEnv = $this->createStub(AccountEnvironment::class);
        $accountEnv
            ->method('getClusterName')
            ->willReturn('clusterName');
        $accountEnv
            ->method('getEnvName')
            ->willReturn('staging');

        $accountWallet = $this->createMock(AccountWallet::class);
        $accountWallet->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$accountEnv]));

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getQueryParams')
            ->willReturn(['cluster' => 'different-cluster~prod']);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan');

        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->exactly(5))
            ->method('set');

        $result = ($this->clusterAndEnvSelection)(
            $manager,
            $request,
            $bag,
            $accountWallet,
        );

        $this->assertInstanceOf(ClusterAndEnvSelection::class, $result);
    }

    public function testInvokeWithAccountWalletAndEmptySelection(): void
    {
        $accountEnv = $this->createMock(AccountEnvironment::class);
        $accountEnv
            ->method('getClusterName')
            ->willReturn('clusterName');
        $accountEnv
            ->method('getEnvName')
            ->willReturn('production');
        $accountEnv->expects($this->once())
            ->method('getNamespace')
            ->willReturn('default-namespace');

        $accountWallet = $this->createMock(AccountWallet::class);
        $accountWallet->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$accountEnv]));

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getQueryParams')
            ->willReturn([]);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan['clusterName'])
                        && 'foo' === $workplan['clusterName']
                        && isset($workplan['envName'])
                        && 'production' === $workplan['envName'];
                })
            );

        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->exactly(5))
            ->method('set')
            ->willReturnCallback(function ($key, $value) use ($bag) {
                if ('namespace' === $key) {
                    $this->assertSame('default-namespace', $value);
                } elseif ('envName' === $key) {
                    $this->assertSame('production', $value);
                }
                return $bag;
            });

        $result = ($this->clusterAndEnvSelection)(
            $manager,
            $request,
            $bag,
            $accountWallet,
        );

        $this->assertInstanceOf(ClusterAndEnvSelection::class, $result);
    }

    public function testInvokeWithNonStringClusterParam(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getQueryParams')
            ->willReturn(['cluster' => 123]);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan['envName'])
                        && '_all' === $workplan['envName'];
                })
            );

        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->exactly(5))
            ->method('set');

        $result = ($this->clusterAndEnvSelection)(
            $manager,
            $request,
            $bag,
        );

        $this->assertInstanceOf(ClusterAndEnvSelection::class, $result);
    }

    public function testInvokeWithEmptyAccountWallet(): void
    {
        $accountWallet = $this->createMock(AccountWallet::class);
        $accountWallet->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([]));

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getQueryParams')
            ->willReturn([]);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return \array_key_exists('clusterName', $workplan)
                        && null === $workplan['clusterName']
                        && isset($workplan['envName'])
                        && '_all' === $workplan['envName'];
                })
            );

        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->exactly(5))
            ->method('set')
            ->willReturnCallback(function ($key, $value) use ($bag) {
                if ('clusterSelected' === $key) {
                    $this->assertNull($value);
                } elseif ('namespace' === $key) {
                    $this->assertSame('_all', $value);
                }
                return $bag;
            });

        $result = ($this->clusterAndEnvSelection)(
            $manager,
            $request,
            $bag,
            $accountWallet,
        );

        $this->assertInstanceOf(ClusterAndEnvSelection::class, $result);
    }
}
