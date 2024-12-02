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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Infrastructures\Twig\SpaceExtension;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Extension\ManagerInterface;
use Teknoo\Space\Infrastructures\Twig\SpaceExtension\Twig;
use Twig\Environment;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(Twig::class)]
class TwigTest extends TestCase
{
    private Twig $twig;

    private ManagerInterface|MockObject $manager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->createMock(ManagerInterface::class);

        $this->twig = new Twig(
            $this->manager
        );
    }

    public function testRun()
    {
        $this->manager
            ->expects($this->once())
            ->method('execute')
            ->willReturnSelf();

        $ext = $this->twig->run(
            $this->createMock(Environment::class),
            'boo'
        );

        self::assertInstanceOf(Twig::class, $ext);
        self::assertnotSame($ext, $this->twig);
    }

    public function testLoad()
    {
        self::assertInstanceOf(Twig::class, $this->twig->load(fn () => null));
    }

    public function testRender()
    {
        self::assertIsString($this->twig->render());
    }
}
