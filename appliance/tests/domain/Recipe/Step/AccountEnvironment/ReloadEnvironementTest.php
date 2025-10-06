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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Object\Persisted\AccountEnvironment;
use Teknoo\Space\Recipe\Step\AccountEnvironment\ReloadEnvironement;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ReloadEnvironement::class)]
class ReloadEnvironementTest extends TestCase
{
    private ReloadEnvironement $reloadEnvironement;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->reloadEnvironement = new ReloadEnvironement();
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            ReloadEnvironement::class,
            ($this->reloadEnvironement)(
                manager: $this->createMock(ManagerInterface::class),
                environment: $this->createMock(AccountEnvironment::class),
            ),
        );
    }

    public function testInvokeWithUpdateWorkPlan(): void
    {
        $environment = $this->createMock(AccountEnvironment::class);
        $environment->expects($this->once())
            ->method('getEnvName')
            ->willReturn('test-env');
        $environment->expects($this->once())
            ->method('getClusterName')
            ->willReturn('test-cluster');
        $environment->expects($this->once())
            ->method('getNamespace')
            ->willReturn('test-namespace');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan['envName'])
                        && 'test-env' === $workplan['envName']
                        && isset($workplan['clusterName'])
                        && 'test-cluster' === $workplan['clusterName']
                        && isset($workplan['kubeNamespace'])
                        && 'test-namespace' === $workplan['kubeNamespace'];
                })
            );

        $this->assertInstanceOf(
            ReloadEnvironement::class,
            ($this->reloadEnvironement)(
                manager: $manager,
                environment: $environment,
            ),
        );
    }
}
