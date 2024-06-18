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
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Liveness;

use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Space\Liveness\PingFile;

use function sys_get_temp_dir;
use function tempnam;

/**
 * Class PingFileTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Liveness\PingFile
 */
class PingFileTest extends TestCase
{
    private PingFile $pingFile;

    private DatesService|MockObject $datesService;

    private string $pingFilePath;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->datesService = $this->createMock(DatesService::class);
        $this->pingFilePath = tempnam(sys_get_temp_dir(), 'pinh');
        $this->pingFile = new PingFile($this->datesService, $this->pingFilePath);
    }

    public function testInvoke(): void
    {
        $this->datesService->expects($this->any())
            ->method('passMeTheDate')
            ->willReturnCallback(
                function (callable $callback) {
                    $callback(new DateTime('2023-03-15'));
                    return $this->datesService;
                }
            );

        self::assertInstanceOf(
            PingFile::class,
            ($this->pingFile)()
        );
    }
}
