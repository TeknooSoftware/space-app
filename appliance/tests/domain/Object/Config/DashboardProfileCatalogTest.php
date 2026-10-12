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

namespace Teknoo\Space\Tests\Unit\Object\Config;

use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Space\Object\Config\DashboardProfile;
use Teknoo\Space\Object\Config\DashboardProfileCatalog;

use function iterator_to_array;

/**
 * Class DashboardProfileCatalogTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(DashboardProfileCatalog::class)]
class DashboardProfileCatalogTest extends TestCase
{
    private DashboardProfile $headlamp;

    private DashboardProfile $legacy;

    private DashboardProfileCatalog $catalog;

    protected function setUp(): void
    {
        parent::setUp();

        $this->headlamp = new DashboardProfile(name: 'headlamp', requestHeaders: []);
        $this->legacy = new DashboardProfile(name: 'kubernetes-dashboard', requestHeaders: []);
        $this->catalog = new DashboardProfileCatalog(
            profiles: ['headlamp' => $this->headlamp, 'kubernetes-dashboard' => $this->legacy],
        );
    }

    public function testGetProfile(): void
    {
        $this->assertSame($this->legacy, $this->catalog->getProfile('kubernetes-dashboard'));
        $this->assertSame($this->headlamp, $this->catalog->getProfile('headlamp'));
    }

    public function testGetProfileWithoutTypeThrows(): void
    {
        $this->expectException(DomainException::class);

        $this->catalog->getProfile('');
    }

    public function testGetProfileWithAnUnknownTypeThrows(): void
    {
        $this->expectException(DomainException::class);

        $this->catalog->getProfile('foo');
    }

    public function testGetIterator(): void
    {
        $this->assertSame(
            ['headlamp' => $this->headlamp, 'kubernetes-dashboard' => $this->legacy],
            iterator_to_array($this->catalog),
        );
    }
}
