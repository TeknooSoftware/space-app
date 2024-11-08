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

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Teknoo\East\Foundation\Time\SleepServiceInterface;
use Teknoo\East\FoundationBundle\Messenger\Client;
use Teknoo\East\FoundationBundle\Messenger\Executor;
use Teknoo\East\Foundation\Http\Message\MessageFactoryInterface;
use Teknoo\East\Paas\Contracts\Recipe\Plan\NewJobInterface;
use Teknoo\East\Paas\Contracts\Security\EncryptionInterface;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Space\Contracts\Object\EncryptableVariableInterface;
use Teknoo\Space\Infrastructures\Symfony\Mercure\Notifier\JobError;
use Teknoo\Space\Infrastructures\Symfony\Messenger\Handler\Exception\BadEncryptionConfigurationException;
use Teknoo\Space\Object\DTO\JobVar;
use Teknoo\Space\Object\DTO\NewJob;
use Teknoo\Space\Service\PersistedVariableEncryption;
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
class NewJobHandler
{
    public function __construct(
        private Executor $executor,
        private NewJobInterface $recipe,
        private MessageFactoryInterface $messageFactory,
        private StreamFactoryInterface $streamFactory,
        private Client $client,
        private LoggerInterface $logger,
        private JobError $jobErrorNotifier,
        private ?EncryptionInterface $encryption,
        private SleepServiceInterface $sleepService,
        private PersistedVariableEncryption $encryptionService,
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

        /** @var Promise<EncryptableVariableInterface, mixed, mixed> $promise */
        $promise = new Promise(
            static function (JobVar $jobVar) use (&$final): void {
                $final[(string) $jobVar->name] = (string) $jobVar->value;
            },
            fn (Throwable $error) => throw $error,
        );

        /** @var JobVar $variable */
        foreach ($variables as $variable) {
            if (empty($variable->encryptionAlgorithm)) {
                $final[(string) $variable->name] = (string) $variable->value;
            } else {
                $this->encryptionService->decrypt(
                    $variable,
                    $promise,
                );
            }
        }

        return json_encode($final, JSON_THROW_ON_ERROR);
    }

    public function __invoke(NewJob $newJob): self
    {
        $client = clone $this->client;
        $client->sendAResponseIsOptional();

        if (0 < $this->waitingTimeSecond) {
            $this->sleepService->wait($this->waitingTimeSecond);
        }

        $currentNewJobId = $newJob->newJobId;

        $processMessage = function (NewJob $newJob) use ($client): void {
            $this->logger->info(
                (string) json_encode(
                    value: [
                        'action' => 'start',
                        'class' => $newJob::class,
                        'projectId' => $newJob->projectId,
                        'envName' => $newJob->envName,
                        'newJobId' => $newJob->newJobId,
                    ],
                    flags: JSON_THROW_ON_ERROR,
                ),
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
        };

        $processError = function (Throwable $error) use ($client, $currentNewJobId): void {
            $this->logger->critical($error);

            if (null !== $currentNewJobId) {
                $this->jobErrorNotifier->process(
                    error: $error,
                    newJobId: $currentNewJobId,
                );
            }

            $client->errorInRequest(
                new UnrecoverableMessageHandlingException(
                    message: $error->getMessage(),
                    code: $error->getCode(),
                    previous: $error,
                )
            );
        };

        if (null !== $this->encryption) {
            /** @var Promise<NewJob, mixed, mixed> $promise */
            $promise = new Promise(
                onSuccess: $processMessage,
                onFail: $processError,
            );

            $this->encryption->decrypt(
                $newJob,
                $promise,
            );

            return $this;
        }

        if (!empty($newJob->getEncryptionAlgorithm())) {
            $processError(
                new BadEncryptionConfigurationException(
                    'teknoo.space.error.messenger.handler.message-can-not-decrypted',
                ),
            );

            return $this;
        }

        try {
            $processMessage($newJob);
        } catch (Throwable $error) {
            $processError($error);
        }

        return $this;
    }
}
