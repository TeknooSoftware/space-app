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

namespace Teknoo\Space\Infrastructures\Symfony\Messenger\Handler;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Teknoo\East\FoundationBundle\Messenger\Client;
use Teknoo\East\FoundationBundle\Messenger\Executor;
use Teknoo\East\Foundation\Http\Message\MessageFactoryInterface;
use Teknoo\East\Paas\Contracts\Recipe\Cookbook\NewJobInterface;
use Teknoo\Space\Object\DTO\NewJob;
use Throwable;

use function json_encode;
use function sleep;

use const JSON_THROW_ON_ERROR;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[AsMessageHandler]
class NewJobHandler
{
    public function __construct(
        private Executor $executor,
        private NewJobInterface $recipe,
        private MessageFactoryInterface $messageFactory,
        private StreamFactoryInterface $streamFactory,
        private Client $client,
        private LoggerInterface $logger,
        private int $waitingTimeSecond = 0,
    ) {
    }

    /**
     * @param array<int, \Teknoo\Space\Object\DTO\JobVar> $variables
     * @return string
     */
    private function convertToJson(array $variables): string
    {
        $final = [];
        foreach ($variables as $variable) {
            $final[(string) $variable->name] = (string) $variable->value;
        }

        return json_encode($final, JSON_THROW_ON_ERROR);
    }

    public function __invoke(NewJob $newJob): self
    {
        $client = clone $this->client;
        $client->sendAResponseIsOptional();

        if (0 < $this->waitingTimeSecond) {
            sleep($this->waitingTimeSecond);
        }

        try {
            $this->logger->info(
                (string) json_encode(
                    [
                        'action' => 'start',
                        'class' => $newJob::class,
                        'projectId' => $newJob->projectId,
                        'envName' => $newJob->envName,
                        'newJobId' => $newJob->newJobId,
                    ]
                )
            );

            $message = $this->messageFactory->createMessage('1.1');
            $message = $message->withBody($this->streamFactory->createStream($this->convertToJson($newJob->variables)));
            $message = $message->withAddedHeader('Content-Type', 'application/json');

            $this->executor->execute(
                $this->recipe,
                $message,
                $client,
                [
                    'projectId' => $newJob->projectId,
                    'envName' => $newJob->envName,
                    'newJobId' => $newJob->newJobId,
                    NewJob::class => $newJob,
                    'extra' => ['new_job_id' => $newJob->newJobId],
                ],
            );
        } catch (Throwable $error) {
            $this->logger->critical($error);

            $client->errorInRequest(
                new UnrecoverableMessageHandlingException(
                    message: $error->getMessage(),
                    code: $error->getCode(),
                    previous: $error,
                )
            );
        }

        return $this;
    }
}
