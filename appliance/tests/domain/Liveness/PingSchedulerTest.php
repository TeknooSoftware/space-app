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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Liveness\PingService;
use Teknoo\East\Foundation\Time\TimerService;
use Teknoo\Space\Liveness\PingFile;
use Teknoo\Space\Liveness\PingScheduler;

/**
 * Class PingSchedulerTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Liveness\PingScheduler
 */
class PingSchedulerTest extends TestCase
{
    private PingScheduler $pingScheduler;

    private PingService|MockObject $pingService;

    private PingFile|MockObject $pingFile;

    private TimerService|MockObject $timerService;

    private int $timerSeconds;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->pingService = $this->createMock(PingService::class);
        $this->pingFile = $this->createMock(PingFile::class);
        $this->timerService = $this->createMock(TimerService::class);
        $this->timerSeconds = 42;
        $this->pingScheduler = new PingScheduler(
            pingService: $this->pingService,
            pingFile: $this->pingFile,
            timerService: $this->timerService,
            timerSeconds: $this->timerSeconds
        );
    }

    public function testTimerAction(): void
    {
        $this->pingService
            ->expects($this->once())
            ->method('ping')
            ->willReturnSelf();

        $this->timerService
            ->expects($this->once())
            ->method('register')
            ->willReturnSelf();

        self::assertInstanceOf(
            PingScheduler::class,
            $this->pingScheduler->timerAction()
        );
    }

    public function testEnable(): void
    {
        $this->pingService
            ->expects($this->once())
            ->method('register')
            ->willReturnSelf();

        $this->timerService
            ->expects($this->once())
            ->method('register')
            ->willReturnSelf();

        self::assertInstanceOf(
            PingScheduler::class,
            $this->pingScheduler->enable()
        );
    }

    public function testDisable(): void
    {
        $this->pingService
            ->expects($this->once())
            ->method('unregister')
            ->willReturnSelf();

        $this->timerService
            ->expects($this->once())
            ->method('unregister')
            ->willReturnSelf();

        self::assertInstanceOf(
            PingScheduler::class,
            $this->pingScheduler->disable()
        );
    }
}
