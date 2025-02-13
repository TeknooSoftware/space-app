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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Event\ConsoleEvent;
use Teknoo\Space\Infrastructures\Symfony\Event\LivenessSubscriber;
use Teknoo\Space\Liveness\PingScheduler;

/**
 * Class LivenessSubscriberTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(LivenessSubscriber::class)]
class LivenessSubscriberTest extends TestCase
{
    private LivenessSubscriber $livenessSubscriber;

    private PingScheduler|MockObject $pingScheduler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->pingScheduler = $this->createMock(PingScheduler::class);
        $this->livenessSubscriber = new LivenessSubscriber($this->pingScheduler);
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertIsArray(
            LivenessSubscriber::getSubscribedEvents(),
        );
    }

    public function testConfigurePing(): void
    {
        $this->pingScheduler
            ->expects($this->once())
            ->method('enable')
            ->willReturnSelf();

        self::assertInstanceOf(
            LivenessSubscriber::class,
            $this->livenessSubscriber->configurePing(
                $this->createMock(ConsoleEvent::class),
            ),
        );
    }
}
