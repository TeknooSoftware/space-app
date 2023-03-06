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

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Teknoo\East\Paas\Infrastructures\Symfony\Messenger\Message\HistorySent;
use Teknoo\East\Paas\Infrastructures\Symfony\Messenger\Message\JobDone;
use Throwable;

use function json_encode;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[AsMessageHandler]
class HistorySentHandler
{
    use HandlerTrait;

    public function __invoke(HistorySent | JobDone $history): self
    {
        $client = clone $this->client;
        $client->sendAResponseIsOptional();

        try {
            $this->logger->info(
                (string) json_encode(
                    [
                        'action' => 'log',
                        'class' => $history::class,
                        'projectId' => $history->getProjectId(),
                        'envName' => $history->getEnvironment(),
                        'jobId' => $history->getJobId()
                    ]
                )
            );

            $message = $this->createMessage($history->getMessage());

            $this->executor->execute(
                $this->recipe,
                $message,
                $client,
                [
                    'projectId' => $history->getProjectId(),
                    'envName' => $history->getEnvironment(),
                    'jobId' => $history->getJobId(),
                ]
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
