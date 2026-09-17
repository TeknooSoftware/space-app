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

namespace Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Mercure;

use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\HubRegistry;
use Symfony\Component\Mercure\Jwt\Grant;
use Symfony\Component\Mercure\ProtocolVersion;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Contracts\Recipe\Step\Job\FetchJobIdFromPendingInterface;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Mercure\Exception\ExceedLimitException;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Mercure\Exception\SSEClosedException;

use function rawurlencode;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class FetchJobIdFromPending implements FetchJobIdFromPendingInterface
{
    public function __construct(
        private readonly HubRegistry $hubRegistry,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EventSourceHttpClient $sseClient,
        private readonly string $topicRoute,
        private readonly int $maxLoopInSSE = 10,
        private readonly int $maxChunkCount = 100,
        private readonly bool $mercureEnabled = true,
    ) {
    }

    private function getTopicUrl(string $taskId): string
    {
        return $this->urlGenerator->generate(
            name: $this->topicRoute,
            parameters: [
                'taskId' => $taskId,
            ],
            referenceType: UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }

    private function getMercureUrl(
        HubInterface $hub,
        string $topicUrl,
        string $taskId,
    ): string {
        // The Mercure protocol 1.0 replaced the `topic` query parameter by matcher-typed ones,
        // `match` being the exact matcher. `lastEventID` is kept by both versions.
        $matcher = match ($hub->getProtocolVersion()) {
            ProtocolVersion::V1 => 'match',
            ProtocolVersion::Legacy => 'topic',
        };

        $url = $hub->getPublicUrl();

        $url .= '?' . $matcher . '=' . rawurlencode($topicUrl);

        return $url . ('&lastEventID=' . $taskId);
    }

    public function __invoke(
        ManagerInterface $manager,
        ParametersBag $parametersBag,
        string $taskId,
    ): FetchJobIdFromPendingInterface {
        if (false === $this->mercureEnabled) {
            $parametersBag->set(
                'taskResult',
                [
                    'task_id' => $taskId,
                    'error_code' => 500,
                    'error_message' => 'teknoo.space.error.job.pending.mercure_disabled',
                ],
            );

            return $this;
        }

        $hub = $this->hubRegistry->getHub();
        $topicUrl = $this->getTopicUrl($taskId);
        $url = $this->getMercureUrl($hub, $topicUrl, $taskId);
        // Grant the subscription on the topic actually listened to: without it the token
        // authorizes nothing and the connection only works on a hub allowing anonymous
        // subscribers to public updates.
        $jwt = $hub->getFactory()?->create(
            [
                new Grant([Grant::ACTION_SUBSCRIBE], [$topicUrl]),
            ],
        );

        $this->sseClient->reset();
        $source = $this->sseClient->connect(
            url: $url,
            options: [
                'auth_bearer' => $jwt,
            ],
        );

        $loopCounter = 0;
        $chunkCounter = 0;
        while ($source instanceof ResponseInterface) {
            foreach ($this->sseClient->stream($source, 2) as $chunk) {
                if ($this->maxChunkCount < ++$chunkCounter) {
                    throw new ExceedLimitException(
                        message: 'teknoo.space.error.job.pending.exceed_sse_chunk_limit',
                        code: 500,
                    );
                }

                if ($chunk->isTimeout()) {
                    continue;
                }

                if ($chunk->isLast()) {
                    $source = null;

                    throw new SSEClosedException(
                        message: 'teknoo.space.error.job.pending.sse_closed',
                        code: 500,
                    );
                }

                // this is a special ServerSentEvent chunk holding the pushed message
                if ($chunk instanceof ServerSentEvent) {
                    $parametersBag->set('taskResult', $chunk->getArrayData());
                    $this->sseClient->reset();

                    $source = null;
                    break;
                }
            }

            if (null !== $source && $this->maxLoopInSSE < ++$loopCounter) {
                throw new ExceedLimitException(
                    message: 'teknoo.space.error.job.pending.exceed_sse_retry_limit',
                    code: 500,
                );
            }
        }

        return $this;
    }
}
