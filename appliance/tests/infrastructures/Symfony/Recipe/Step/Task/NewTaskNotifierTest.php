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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Recipe\Step\Task;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Infrastructures\Symfony\Mercure\JobUrlPublisher;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Task\NewTaskNotifier;
use Teknoo\Space\Object\DTO\JobVar;
use Teknoo\Space\Object\DTO\NewJob;

/**
 * Class NewTaskNotifierTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(NewTaskNotifier::class)]
class NewTaskNotifierTest extends TestCase
{
    private NewTaskNotifier $newTaskNotifier;

    private JobUrlPublisher&Stub $publisher;

    private UrlGeneratorInterface&Stub $generator;

    private LoggerInterface&Stub $logger;

    private string $pendingTaskRoute;

    private string $spaceDashoardRoute;

    private string $listJobRoute;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->publisher = $this->createStub(JobUrlPublisher::class);
        $this->generator = $this->createStub(UrlGeneratorInterface::class);
        $this->logger = $this->createStub(LoggerInterface::class);
        $this->pendingTaskRoute = '42';
        $this->listJobRoute = '42';
        $this->spaceDashoardRoute = '42';
        $this->newTaskNotifier = new NewTaskNotifier(
            $this->publisher,
            $this->generator,
            $this->pendingTaskRoute,
            $this->listJobRoute,
            $this->spaceDashoardRoute,
            $this->logger,
        );
    }

    public function testInvoke(): void
    {
        $newJob = new NewJob(
            taskId: 'foo',
            variables: [
                new JobVar('foo'),
            ],
        );

        $this->assertInstanceOf(
            NewTaskNotifier::class,
            ($this->newTaskNotifier)(
                $newJob,
                $this->createStub(ManagerInterface::class),
            )
        );
    }
}
