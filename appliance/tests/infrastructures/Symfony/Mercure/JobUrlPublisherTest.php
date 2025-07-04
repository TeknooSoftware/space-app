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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Mercure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mercure\HubInterface;
use Teknoo\Space\Infrastructures\Symfony\Mercure\JobUrlPublisher;

/**
 * Class JobUrlPublisherTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(JobUrlPublisher::class)]
class JobUrlPublisherTest extends TestCase
{
    private JobUrlPublisher $jobUrlPublisher;

    private HubInterface|MockObject $hub;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->hub = $this->createMock(HubInterface::class);

        $this->jobUrlPublisher = new JobUrlPublisher($this->hub);
    }

    public function testPublish(): void
    {
        self::assertInstanceOf(
            JobUrlPublisher::class,
            $this->jobUrlPublisher->publish(
                'foo',
                'bar',
                'foo',
            )
        );
    }
}
