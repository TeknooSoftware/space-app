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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Messenger\Handler;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Teknoo\East\Foundation\Time\SleepServiceInterface;
use Teknoo\East\FoundationBundle\Messenger\Client;
use Teknoo\East\FoundationBundle\Messenger\Executor;
use Teknoo\East\Foundation\Http\Message\MessageFactoryInterface;
use Teknoo\East\Paas\Contracts\Recipe\Plan\NewJobInterface;
use Teknoo\East\Paas\Contracts\Security\EncryptionInterface;
use Teknoo\Space\Infrastructures\Symfony\Mercure\Notifier\JobError;
use Teknoo\Space\Infrastructures\Symfony\Messenger\Handler\NewJobHandler;
use Teknoo\Space\Object\DTO\NewJob;
use Teknoo\Space\Service\PersistedVariableEncryption;

/**
 * Class NewJobHandlerTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(NewJobHandler::class)]
class NewJobHandlerTest extends TestCase
{
    private NewJobHandler $newJobHandler;

    private Executor|MockObject $executor;

    private NewJobInterface|MockObject $recipe;

    private MessageFactoryInterface|MockObject $messageFactory;

    private StreamFactoryInterface|MockObject $streamFactory;

    private Client|MockObject $client;

    private LoggerInterface|MockObject $logger;

    private JobError|MockObject $jobError;

    private EncryptionInterface|MockObject $encryption;

    private SleepServiceInterface|MockObject $sleepService;

    private PersistedVariableEncryption|MockObject $persistedVariableEncryption;

    private int $waitingTimeSecond;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->executor = $this->createMock(Executor::class);
        $this->recipe = $this->createMock(NewJobInterface::class);
        $this->messageFactory = $this->createMock(MessageFactoryInterface::class);
        $this->streamFactory = $this->createMock(StreamFactoryInterface::class);
        $this->client = $this->createMock(Client::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->jobError = $this->createMock(JobError::class);
        $this->encryption = $this->createMock(EncryptionInterface::class);
        $this->sleepService = $this->createMock(SleepServiceInterface::class);
        $this->persistedVariableEncryption = $this->createMock(PersistedVariableEncryption::class);
        $this->waitingTimeSecond = 1;
        $this->newJobHandler = new NewJobHandler(
            $this->executor,
            $this->recipe,
            $this->messageFactory,
            $this->streamFactory,
            $this->client,
            $this->logger,
            $this->jobError,
            $this->encryption,
            $this->sleepService,
            $this->persistedVariableEncryption,
            $this->waitingTimeSecond,
        );
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            NewJobHandler::class,
            ($this->newJobHandler)(
                new NewJob(newJobId: 'foo'),
            )
        );
    }
}
