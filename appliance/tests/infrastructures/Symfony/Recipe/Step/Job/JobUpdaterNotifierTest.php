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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Recipe\Step\Job;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teknoo\East\Paas\Object\Job;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Infrastructures\Symfony\Mercure\JobUrlPublisher;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Job\JobUpdaterNotifier;

/**
 * Class JobUpdaterNotifierTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Job\JobUpdaterNotifier
 */
class JobUpdaterNotifierTest extends TestCase
{
    private JobUpdaterNotifier $jobUpdaterNotifier;

    private JobUrlPublisher|MockObject $publisher;

    private UrlGeneratorInterface|MockObject $generator;

    private string $pendingJobRoute;

    private string $getJobRoute;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->publisher = $this->createMock(JobUrlPublisher::class);
        $this->generator = $this->createMock(UrlGeneratorInterface::class);
        $this->pendingJobRoute = '42';
        $this->getJobRoute = '42';
        $this->jobUpdaterNotifier = new JobUpdaterNotifier(
            $this->publisher,
            $this->generator,
            $this->pendingJobRoute,
            $this->getJobRoute
        );
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            JobUpdaterNotifier::class,
            ($this->jobUpdaterNotifier)(
                project: $this->createMock(Project::class),
                job: $this->createMock(Job::class),
                newJobId: 'foo',
            )
        );
    }
}
