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

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Teknoo\East\Paas\Infrastructures\Symfony\Messenger\Message\HistorySent;
use Teknoo\East\Paas\Infrastructures\Symfony\Messenger\Message\JobDone;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Infrastructures\Symfony\Messenger\Handler\Exception\BadEncryptionConfigurationException;
use Throwable;

use function json_encode;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
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

        $processMessage = function (HistorySent | JobDone $history) use ($client): void {
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
        };

        $processError = function (Throwable $error) use ($client): void {
            $this->logger->critical($error);

            $client->errorInRequest(
                new UnrecoverableMessageHandlingException(
                    message: $error->getMessage(),
                    code: $error->getCode(),
                    previous: $error,
                )
            );
        };

        if (null !== $this->encryption) {
            /** @var Promise<HistorySent|JobDone, mixed, mixed> $promise */
            $promise = new Promise(
                onSuccess: $processMessage,
                onFail: $processError,
            );

            $this->encryption->decrypt(
                $history,
                $promise,
            );

            return $this;
        }

        if (!empty($history->getEncryptionAlgorithm())) {
            $processError(
                new BadEncryptionConfigurationException(
                    'teknoo.space.error.messenger.handler.message-can-not-decrypted',
                ),
            );

            return $this;
        }

        try {
            $processMessage($history);
        } catch (Throwable $error) {
            $processError($error);
        }

        return $this;
    }
}
