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
use Teknoo\East\Paas\Infrastructures\Symfony\Messenger\Message\MessageJob;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Infrastructures\Symfony\Messenger\Handler\Exception\BadEncryptionConfigurationException;
use Throwable;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[AsMessageHandler]
class RunJobHandler
{
    use HandlerTrait;

    public function __invoke(MessageJob $job): self
    {
        $client = clone $this->client;
        $client->sendAResponseIsOptional();

        $processMessage = function (MessageJob $job) use ($client): void {
            $this->logger->info(
                (string) json_encode(
                    value: [
                        'action' => 'run',
                        'class' => $job::class,
                        'projectId' => $job->getProjectId(),
                        'envName' => $job->getEnvironment(),
                        'jobId' => $job->getJobId(),
                    ],
                    flags: JSON_THROW_ON_ERROR,
                )
            );

            $message = $this->createMessage($job->getMessage());

            $this->executor->execute(
                $this->recipe,
                $message,
                $client,
                [
                    'projectId' => $job->getProjectId(),
                    'envName' => $job->getEnvironment(),
                    'jobId' => $job->getJobId(),
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
            /** @var Promise<MessageJob, mixed, mixed> $promise */
            $promise = new Promise(
                onSuccess: $processMessage,
                onFail: $processError,
            );

            $this->encryption->decrypt(
                $job,
                $promise,
            );

            return $this;
        }

        if (!empty($job->getEncryptionAlgorithm())) {
            $processError(
                new BadEncryptionConfigurationException(
                    'teknoo.space.error.messenger.handler.message-can-not-decrypted',
                ),
            );

            return $this;
        }

        try {
            $processMessage($job);
        } catch (Throwable $error) {
            $processError($error);
        }

        return $this;
    }
}
