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

namespace Teknoo\Space\Infrastructures\Symfony\Messenger\Handler;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Teknoo\East\FoundationBundle\Messenger\Client;
use Teknoo\East\FoundationBundle\Messenger\Executor;
use Teknoo\East\Foundation\Http\Message\MessageFactoryInterface;
use Teknoo\Recipe\BaseRecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait HandlerTrait
{
    public function __construct(
        private Executor $executor,
        private BaseRecipeInterface $recipe,
        private MessageFactoryInterface $messageFactory,
        private StreamFactoryInterface $streamFactory,
        private Client $client,
        private LoggerInterface $logger,
    ) {
    }

    private function createMessage(string $content): MessageInterface
    {
        $message = $this->messageFactory->createMessage('1.1');
        $message = $message->withBody($this->streamFactory->createStream($content));
        return $message->withAddedHeader('Content-Type', 'application/json');
    }
}
