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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Twig\Extension;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Teknoo\Space\Infrastructures\Twig\Extension\ApiCollectionSerializing;
use ArrayIterator;

/**
 * Class ApiCollectionSerializingTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ApiCollectionSerializing::class)]
class ApiCollectionSerializingTest extends TestCase
{
    private ApiCollectionSerializing $collectionSerializing;

    private SerializerInterface&Stub $serializer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->serializer = $this->createStub(SerializerInterface::class);

        $this->collectionSerializing = new ApiCollectionSerializing(
            $this->serializer,
        );
    }

    public function testGetName(): void
    {
        $this->assertIsString(
            $this->collectionSerializing->serialize([], 1, 2),
        );
    }

    public function testSerializeAGenerator(): void
    {
        $this->serializer
            ->method('serialize')
            ->willReturnCallback(
                fn (mixed $data): string => json_encode($data, JSON_THROW_ON_ERROR)
            );

        $generator = (static function (): iterable {
            yield 'a';
            yield 'b';
        })();

        $this->assertSame(
            '{"meta":{"totalPages":2,"page":1,"count":2},"data":["a","b"]}',
            $this->collectionSerializing->serialize($generator, 1, 2),
        );
    }

    public function testSerializeACountableIterator(): void
    {
        $this->serializer
            ->method('serialize')
            ->willReturnCallback(
                fn (mixed $data): string => json_encode($data, JSON_THROW_ON_ERROR)
            );

        $this->assertSame(
            '{"meta":{"totalPages":3,"page":2,"count":3,"foo":"bar"},"data":["a","b","c"]}',
            $this->collectionSerializing->serialize(new ArrayIterator(['a', 'b', 'c']), 2, 3, meta: ['foo' => 'bar']),
        );
    }
}
