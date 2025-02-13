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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Recipe\Step\Job;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Contracts\Security\EncryptionInterface;
use Teknoo\East\Paas\Object\Project;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Job\CallNewJob;
use Teknoo\Space\Object\DTO\JobVar;
use Teknoo\Space\Object\DTO\NewJob;
use Teknoo\Space\Object\DTO\SpaceProject;

/**
 * Class CallNewJobTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(CallNewJob::class)]
class CallNewJobTest extends TestCase
{
    private CallNewJob $callNewJob;

    private MessageBusInterface|MockObject $messageBus;

    private EncryptionInterface|MockObject $encryption;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->encryption = $this->createMock(EncryptionInterface::class);
        $this->callNewJob = new CallNewJob(
            $this->messageBus,
            $this->encryption,
        );
    }

    public function testInvoke(): void
    {
        $newJob = new NewJob(
            newJobId: 'foo',
            variables: [
                new JobVar('foo'),
            ],
        );

        $this->messageBus
            ->expects($this->any())
            ->method('dispatch')
            ->willReturn(new Envelope($newJob));

        self::assertInstanceOf(
            CallNewJob::class,
            ($this->callNewJob)(
                $this->createMock(ManagerInterface::class),
                $newJob,
                new SpaceProject($this->createMock(Project::class)),
                $this->createMock(ParametersBag::class),
            )
        );
    }
}
