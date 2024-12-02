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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Mercure\Notifier;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teknoo\Space\Infrastructures\Symfony\Mercure\JobErrorPublisher;
use Teknoo\Space\Infrastructures\Symfony\Mercure\Notifier\JobError;

/**
 * Class JobErrorNotifierTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(JobError::class)]
class JobErrorTest extends TestCase
{
    private JobError $jobErrorNotifier;

    private JobErrorPublisher|MockObject $publisher;

    private UrlGeneratorInterface|MockObject $generator;

    private string $pendingJobRoute;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->publisher = $this->createMock(JobErrorPublisher::class);
        $this->generator = $this->createMock(UrlGeneratorInterface::class);
        $this->pendingJobRoute = '42';
        $this->jobErrorNotifier = new JobError($this->publisher, $this->generator, $this->pendingJobRoute);
    }

    public function testProcess(): void
    {
        self::assertInstanceOf(
            JobError::class,
            ($this->jobErrorNotifier)->process(
                new Exception('foo'),
                'bar',
            )
        );
    }
}
