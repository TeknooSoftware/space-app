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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\ClusterConfig;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Object\Config\Cluster;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Recipe\Step\ClusterConfig\SelectClusterConfig;

/**
 * Class SelectClusterConfigTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SelectClusterConfig::class)]
class SelectClusterConfigTest extends TestCase
{
    private SelectClusterConfig $selectClusterConfig;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->selectClusterConfig = new SelectClusterConfig();
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            SelectClusterConfig::class,
            ($this->selectClusterConfig)(
                $this->createStub(ManagerInterface::class),
                new ClusterCatalog(['foo' => $this->createStub(Cluster::class)], []),
                'foo',
            ),
        );
    }
}
