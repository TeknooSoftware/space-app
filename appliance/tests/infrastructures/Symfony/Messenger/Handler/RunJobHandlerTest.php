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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Messenger\Handler;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Teknoo\East\FoundationBundle\Messenger\Client;
use Teknoo\East\FoundationBundle\Messenger\Executor;
use Teknoo\East\Foundation\Http\Message\MessageFactoryInterface;
use Teknoo\East\Paas\Contracts\Security\EncryptionInterface;
use Teknoo\East\Paas\Infrastructures\Symfony\Messenger\Message\MessageJob;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Space\Infrastructures\Symfony\Messenger\Handler\RunJobHandler;

/**
 * Class RunJobHandlerTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(RunJobHandler::class)]
class RunJobHandlerTest extends TestCase
{
    private RunJobHandler $runJobHandler;

    private Executor&Stub $executor;

    private BaseRecipeInterface&Stub $recipe;

    private MessageFactoryInterface&Stub $messageFactory;

    private StreamFactoryInterface&Stub $streamFactory;

    private Client&Stub $client;

    private LoggerInterface&Stub $logger;

    private EncryptionInterface&Stub $encryption;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->executor = $this->createStub(Executor::class);
        $this->recipe = $this->createStub(BaseRecipeInterface::class);
        $this->messageFactory = $this->createStub(MessageFactoryInterface::class);
        $this->streamFactory = $this->createStub(StreamFactoryInterface::class);
        $this->client = $this->createStub(Client::class);
        $this->logger = $this->createStub(LoggerInterface::class);
        $this->encryption = $this->createStub(EncryptionInterface::class);

        $this->runJobHandler = new RunJobHandler(
            $this->executor,
            $this->recipe,
            $this->messageFactory,
            $this->streamFactory,
            $this->client,
            $this->logger,
            $this->encryption,
        );
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            RunJobHandler::class,
            ($this->runJobHandler)(
                $this->createStub(MessageJob::class),
            )
        );
    }
}
