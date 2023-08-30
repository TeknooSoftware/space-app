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

namespace Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Mercure;

use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\HubRegistry;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Contracts\Recipe\Step\Job\FetchJobIdFromPendingInterface;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Mercure\Exception\ExceedLimitException;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Mercure\Exception\SSEClosedException;

use function rawurlencode;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
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

    private function getMercureUrl(
        HubInterface $hub,
        string $newJobId,
    ): string {
        $url = $hub->getUrl();

        $url .= '?topic=' . rawurlencode(
            $this->urlGenerator->generate(
                name: $this->topicRoute,
                parameters: [
                    'newJobId' => $newJobId,
                ],
                referenceType: UrlGeneratorInterface::ABSOLUTE_URL,
            )
        );

        $url .= '&lastEventID=' . $newJobId;

        return $url;
    }

    public function __invoke(
        ManagerInterface $manager,
        ParametersBag $parametersBag,
        string $newJobId,
    ): FetchJobIdFromPendingInterface {
        if (false === $this->mercureEnabled) {
            $parametersBag->set(
                'newJobResult',
                [
                    'job_id' => $newJobId,
                    'error_code' => 500,
                    'error_message' => 'teknoo.space.error.job.pending.mercure_disabled',
                ],
            );

            return $this;
        }

        $hub = $this->hubRegistry->getHub();
        $url = $this->getMercureUrl($hub, $newJobId);
        $jwt = $hub->getProvider()->getJwt();

        $this->sseClient->reset();
        $source = $this->sseClient->connect(
            url: $url,
            options: [
                'auth_bearer' => $jwt,
            ],
        );

        $loopCounter = 0;
        $chunkCounter = 0;
        while ($source) {
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
                    $parametersBag->set('newJobResult', $chunk->getArrayData());
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
