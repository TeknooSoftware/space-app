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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Mercure;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mercure\HubInterface;
use Teknoo\Space\Infrastructures\Symfony\Mercure\JobErrorPublisher;

/**
 * Class JobErrorPublisherTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Infrastructures\Symfony\Mercure\JobErrorPublisher
 */
class JobErrorPublisherTest extends TestCase
{
    private JobErrorPublisher $jobErrorPublisher;

    private HubInterface|MockObject $hub;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->hub = $this->createMock(HubInterface::class);
        $this->jobErrorPublisher = new JobErrorPublisher($this->hub);
    }

    public function testPublish(): void
    {
        self::assertInstanceOf(
            JobErrorPublisher::class,
            $this->jobErrorPublisher->publish(
                'foo',
                'bar',
                new Exception('foo'),
            )
        );
    }
}
