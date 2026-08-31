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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Mercure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mercure\HubInterface;
use Teknoo\Space\Infrastructures\Symfony\Mercure\TaskUrlPublisher;

/**
 * Class TaskUrlPublisherTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(TaskUrlPublisher::class)]
class TaskUrlPublisherTest extends TestCase
{
    private TaskUrlPublisher $taskUrlPublisher;

    private HubInterface&Stub $hub;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->hub = $this->createStub(HubInterface::class);

        $this->taskUrlPublisher = new TaskUrlPublisher($this->hub);
    }

    public function testPublish(): void
    {
        $this->assertInstanceOf(
            TaskUrlPublisher::class,
            $this->taskUrlPublisher->publish(
                'foo',
                'bar',
                'foo',
            )
        );
    }
}
