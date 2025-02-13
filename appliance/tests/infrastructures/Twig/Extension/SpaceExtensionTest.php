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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Twig\Extension;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\Space\Infrastructures\Twig\Extension\SpaceExtension;
use Teknoo\Space\Infrastructures\Twig\SpaceExtension\Twig as TwigExtension;
use Twig\Environment;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SpaceExtension::class)]
class SpaceExtensionTest extends TestCase
{
    private SpaceExtension $spaceExtension;

    private TwigExtension|MockObject $twig;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->twig = $this->createMock(TwigExtension::class);

        $this->spaceExtension = new SpaceExtension(
            $this->twig
        );
    }

    public function testGetFunctions()
    {
        self::assertIsArray($this->spaceExtension->getFunctions());
    }

    public function testGetName()
    {
        self::assertEquals('space_extension', $this->spaceExtension->getName());
    }

    public function testRunExtension()
    {
        self::assertIsString(
            $this->spaceExtension->runExtension(
                $this->createMock(Environment::class),
                'foo'
            )
        );
    }
}
