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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Endroid\QrCode\Recipe\Step;

use Endroid\QrCode\Writer\PngWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Infrastructures\Endroid\QrCode\Recipe\Step\BuildQrCode;

/**
 * Class BuildQrCodeTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(BuildQrCode::class)]
class BuildQrCodeTest extends TestCase
{
    private BuildQrCode $buildQrCode;

    private PngWriter|MockObject $pngWriter;

    private StreamFactoryInterface|MockObject $streamFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->pngWriter = new PngWriter();
        $this->streamFactory = $this->createMock(StreamFactoryInterface::class);
        $this->buildQrCode = new BuildQrCode($this->pngWriter, $this->streamFactory);
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            BuildQrCode::class,
            ($this->buildQrCode)(
                $this->createMock(ManagerInterface::class),
                $this->createMock(ClientInterface::class),
                'foo',
            ),
        );
    }
}
