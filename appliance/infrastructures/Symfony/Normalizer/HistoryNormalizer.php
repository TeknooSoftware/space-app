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

namespace Teknoo\Space\Infrastructures\Symfony\Normalizer;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Teknoo\East\Paas\Object\History;

use function in_array;
use function is_array;
use function is_string;

/**
 * To add, in the API, to each entry of an history (and to each of its previous entries), an `humanized_message` key,
 * holding the translation of its message (most of time the class name of the step which has written it), next to the
 * untouched keys produced by `History::jsonSerialize()`. A message without translation is kept as is.
 * Only applied with the `api` serialization group, the payloads exchanged with workers are left untouched.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class HistoryNormalizer implements NormalizerInterface
{
    private const string API_GROUP = 'api';

    private const string MESSAGE_KEY = 'message';

    private const string HUMANIZED_MESSAGE_KEY = 'humanized_message';

    private const string PREVIOUS_KEY = 'previous';

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @param array<mixed> $entry
     * @return array<mixed>
     */
    private function humanize(array $entry): array
    {
        if (isset($entry[self::MESSAGE_KEY]) && is_string($entry[self::MESSAGE_KEY])) {
            $entry = [
                self::MESSAGE_KEY => $entry[self::MESSAGE_KEY],
                self::HUMANIZED_MESSAGE_KEY => $this->translator->trans($entry[self::MESSAGE_KEY]),
            ] + $entry;
        }

        if (isset($entry[self::PREVIOUS_KEY]) && is_array($entry[self::PREVIOUS_KEY])) {
            $entry[self::PREVIOUS_KEY] = $this->humanize($entry[self::PREVIOUS_KEY]);
        }

        return $entry;
    }

    /**
     * @param array<string, mixed> $context
     * @return array<mixed>
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        if (!$data instanceof History) {
            throw new InvalidArgumentException('The normalized object must be an instance of ' . History::class);
        }

        return $this->humanize($data->jsonSerialize());
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof History
            && in_array(self::API_GROUP, (array) ($context['groups'] ?? []), true);
    }

    /**
     * @return array<class-string, bool>
     */
    public function getSupportedTypes(?string $format): array
    {
        //Not cacheable, the support depends on the serialization groups in the context
        return [
            History::class => false,
        ];
    }
}
