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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\AccountEnvironment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Recipe\Step\AccountEnvironment\FindEnvironmentInWallet;

use function array_key_exists;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(FindEnvironmentInWallet::class)]
class FindEnvironmentInWalletTest extends TestCase
{
    private FindEnvironmentInWallet $extractResumes;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->extractResumes = new FindEnvironmentInWallet();
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            FindEnvironmentInWallet::class,
            ($this->extractResumes)(
                manager: $this->createStub(ManagerInterface::class),
                wallet: $this->createStub(AccountWallet::class),
                envName: 'foo',
                clusterName: 'bar',
            ),
        );
    }

    public function testInvokeWithWalletGetAndUpdateWorkPlan(): void
    {
        $environment = $this->createStub(AccountEnvironment::class);
        $wallet = $this->createMock(AccountWallet::class);
        $wallet->expects($this->once())
            ->method('get')
            ->with('cluster-name', 'env-name')
            ->willReturn($environment);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) use ($environment) {
                    return isset($workplan[AccountEnvironment::class])
                        && $workplan[AccountEnvironment::class] === $environment;
                })
            );

        $this->assertInstanceOf(
            FindEnvironmentInWallet::class,
            ($this->extractResumes)(
                manager: $manager,
                wallet: $wallet,
                envName: 'env-name',
                clusterName: 'cluster-name',
            ),
        );
    }

    public function testInvokeWithNullEnvironment(): void
    {
        $wallet = $this->createMock(AccountWallet::class);
        $wallet->expects($this->once())
            ->method('get')
            ->with('test-cluster', 'test-env')
            ->willReturn(null);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return array_key_exists(AccountEnvironment::class, $workplan)
                        && null === $workplan[AccountEnvironment::class];
                })
            );

        $this->assertInstanceOf(
            FindEnvironmentInWallet::class,
            ($this->extractResumes)(
                manager: $manager,
                wallet: $wallet,
                envName: 'test-env',
                clusterName: 'test-cluster',
            ),
        );
    }
}
