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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Kubernetes\Recipe\Step\Misc;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Infrastructures\Kubernetes\Recipe\Step\Misc\Health;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\Config\ConfigClusterInterface;

/**
 * Class HealthTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(Health::class)]
class HealthTest extends TestCase
{
    private Health $health;


    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->health = new Health($this->createStub(ClusterCatalog::class));
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            Health::class,
            ($this->health)(
                $this->createStub(ManagerInterface::class),
                $this->createStub(ParametersBag::class),
            )
        );
    }

    public function testInvokeSkipsNonKubernetesCluster(): void
    {
        $nonK8s = new class implements ConfigClusterInterface {
            public string $name = 'foo';

            public string $sluggyName = 'foo';

            public string $type = 'docker-compose';

            public string $masterAddress = 'ssh://u@h:22';

            public string $dashboardAddress = 'foo';

            public bool $supportRegistry = false;

            public bool $useHnc = false;

            public bool $isExternal = false;
        };

        $health = new Health(new ClusterCatalog(['foo' => $nonK8s], []));

        //Non-Kubernetes clusters have no Kubernetes API, so they are skipped from the health overview
        //(no exception) and never appear in the "k8s" parameter.
        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->once())
            ->method('set')
            ->with('k8s', []);

        $this->assertInstanceOf(
            Health::class,
            $health(
                $this->createStub(ManagerInterface::class),
                $bag,
            ),
        );
    }
}
