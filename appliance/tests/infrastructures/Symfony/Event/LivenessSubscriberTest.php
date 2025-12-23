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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Event\ConsoleEvent;
use Teknoo\Space\Infrastructures\Symfony\Event\LivenessSubscriber;
use Teknoo\Space\Liveness\PingScheduler;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

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

    private PingScheduler&MockObject $pingScheduler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->pingScheduler = $this->createMock(PingScheduler::class);
        $this->livenessSubscriber = new LivenessSubscriber($this->pingScheduler);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetSubscribedEvents(): void
    {
        $this->assertIsArray(
            LivenessSubscriber::getSubscribedEvents(),
        );
    }

    public function testConfigurePing(): void
    {
        $this->pingScheduler
            ->expects($this->once())
            ->method('enable')
            ->willReturnSelf();

        $this->assertInstanceOf(
            LivenessSubscriber::class,
            $this->livenessSubscriber->configurePing(
                $this->createStub(ConsoleEvent::class),
            ),
        );
    }
}
