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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Teknoo\East\FoundationBundle\Messenger\Client;
use Teknoo\East\FoundationBundle\Messenger\Executor;
use Teknoo\East\Foundation\Http\Message\MessageFactoryInterface;
use Teknoo\East\Paas\Infrastructures\Symfony\Messenger\Message\HistorySent;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Space\Infrastructures\Symfony\Messenger\Handler\HistorySentHandler;

/**
 * Class HistorySentHandlerTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Infrastructures\Symfony\Messenger\Handler\HistorySentHandler
 */
class HistorySentHandlerTest extends TestCase
{
    private HistorySentHandler $historySentHandler;

    private Executor|MockObject $executor;

    private BaseRecipeInterface|MockObject $recipe;

    private MessageFactoryInterface|MockObject $messageFactory;

    private StreamFactoryInterface|MockObject $streamFactory;

    private Client|MockObject $client;

    private LoggerInterface|MockObject $logger;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->executor = $this->createMock(Executor::class);
        $this->recipe = $this->createMock(BaseRecipeInterface::class);
        $this->messageFactory = $this->createMock(MessageFactoryInterface::class);
        $this->streamFactory = $this->createMock(StreamFactoryInterface::class);
        $this->client = $this->createMock(Client::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->historySentHandler = new HistorySentHandler(
            $this->executor,
            $this->recipe,
            $this->messageFactory,
            $this->streamFactory,
            $this->client,
            $this->logger,
        );
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            HistorySentHandler::class,
            ($this->historySentHandler)(
                $this->createMock(HistorySent::class),
            )
        );
    }
}
