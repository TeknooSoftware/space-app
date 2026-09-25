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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Normalizer;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Teknoo\East\Paas\Object\History;
use Teknoo\Space\Infrastructures\Symfony\Normalizer\HistoryNormalizer;

/**
 * Class HistoryNormalizerTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(HistoryNormalizer::class)]
class HistoryNormalizerTest extends TestCase
{
    private HistoryNormalizer $normalizer;

    private TranslatorInterface&Stub $translator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translator = $this->createStub(TranslatorInterface::class);
        $this->translator
            ->method('trans')
            ->willReturnCallback(
                fn (string $id): string => match ($id) {
                    'Foo\Bar\CloneRepository' => 'Cloning the source repository',
                    default => $id,
                }
            );

        $this->normalizer = new HistoryNormalizer(
            $this->translator,
        );
    }

    private function createHistory(): History
    {
        return new History(
            previous: new History(
                previous: null,
                message: 'Foo\Bar\CloneRepository',
                date: new DateTimeImmutable('2026-09-23 10:00:00 UTC'),
                serialNumber: 1,
            ),
            message: 'unknown message',
            date: new DateTimeImmutable('2026-09-23 10:01:00 UTC'),
            isFinal: true,
            extra: ['result' => []],
            serialNumber: 2,
        );
    }

    public function testSupportsHistoryInTheApiGroup(): void
    {
        $this->assertTrue(
            $this->normalizer->supportsNormalization($this->createHistory(), 'json', ['groups' => ['api']]),
        );
    }

    public function testSupportsHistoryWhenTheGroupIsAString(): void
    {
        $this->assertTrue(
            $this->normalizer->supportsNormalization($this->createHistory(), 'json', ['groups' => 'api']),
        );
    }

    public function testDoesNotSupportHistoryOutsideTheApiGroup(): void
    {
        $this->assertFalse(
            $this->normalizer->supportsNormalization($this->createHistory(), 'json', ['groups' => ['default']]),
        );

        $this->assertFalse(
            $this->normalizer->supportsNormalization($this->createHistory(), 'json'),
        );
    }

    public function testDoesNotSupportOtherObjects(): void
    {
        $this->assertFalse(
            $this->normalizer->supportsNormalization(new stdClass(), 'json', ['groups' => ['api']]),
        );
    }

    public function testGetSupportedTypes(): void
    {
        $this->assertSame(
            [History::class => false],
            $this->normalizer->getSupportedTypes('json'),
        );
    }

    public function testNormalizeWithANonHistoryObject(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->normalizer->normalize(new stdClass(), 'json', ['groups' => ['api']]);
    }

    public function testNormalize(): void
    {
        $this->assertSame(
            [
                'message' => 'unknown message',
                'humanized_message' => 'unknown message',
                'date' => '2026-09-23 10:01:00 UTC',
                'is_final' => true,
                'extra' => ['result' => []],
                'previous' => [
                    'message' => 'Foo\Bar\CloneRepository',
                    'humanized_message' => 'Cloning the source repository',
                    'date' => '2026-09-23 10:00:00 UTC',
                    'is_final' => false,
                    'extra' => [],
                    'previous' => null,
                    'serial_number' => 1,
                ],
                'serial_number' => 2,
            ],
            $this->normalizer->normalize($this->createHistory(), 'json', ['groups' => ['api']]),
        );
    }
}
