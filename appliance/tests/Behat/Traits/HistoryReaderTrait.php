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

namespace Teknoo\Space\Tests\Behat\Traits;

use PHPUnit\Framework\Assert;
use Symfony\Component\DomCrawler\Crawler;
use Teknoo\East\Paas\Object\History;

use function is_array;
use function str_contains;
use function trim;

/**
 * Readers of a `History` chain (jobs, account histories, and the histories of the extensions), shared by every
 * Behat context: the chain itself, its humanized rendering on a page and its humanized serialization.
 *
 * Helpers only, without any step definition: a context of an extension can use it next to the core contexts
 * without declaring a step twice.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
trait HistoryReaderTrait
{
    /**
     * Every entry of a history chain, the most recent first.
     *
     * @return list<History>
     */
    private function historyEntries(?History $history): array
    {
        $entries = [];
        while (null !== $history) {
            $entries[] = $history;
            $history = $history->getPrevious();
        }

        return $entries;
    }

    /**
     * Rows of a history table rendered by a `displayJobHistory` macro: each row shows the humanized label, then
     * the raw message (a class name or a translation key) on a secondary line. A row flagged `text-warning` is a
     * warning.
     *
     * @return array{labels: array<string, string>, warnings: list<string>}
     */
    private function readHumanizedHistoryRows(Crawler $crawler): array
    {
        $labels = [];
        $warnings = [];
        $crawler->filter('tbody tr')->each(
            static function (Crawler $row) use (&$labels, &$warnings): void {
                $message = $row->filter('td.col-10 > div.small.fst-italic.text-muted');
                if (0 === $message->count()) {
                    return;
                }

                $message = trim($message->text());
                //The humanized label is the text before the secondary line holding the raw message
                $labels[$message] = trim((string) $row->filter('td.col-10')->getNode(0)?->firstChild?->textContent);
                if (str_contains((string) $row->attr('class'), 'text-warning')) {
                    $warnings[] = $message;
                }
            }
        );

        return ['labels' => $labels, 'warnings' => $warnings];
    }

    /**
     * The `humanized_message` of every entry of a serialized history chain, keyed by its raw message. Every entry
     * must carry both.
     *
     * @return array<string, string>
     */
    private function collectHumanizedMessages(mixed $entry): array
    {
        $humanizedMessages = [];
        while (is_array($entry)) {
            Assert::assertIsString($entry['message'] ?? null);
            Assert::assertIsString($entry['humanized_message'] ?? null);

            $humanizedMessages[$entry['message']] = $entry['humanized_message'];
            $entry = $entry['previous'] ?? null;
        }

        return $humanizedMessages;
    }
}
