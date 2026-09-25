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
use Teknoo\Space\Infrastructures\Twig\Extension\ApiObjectSerializing;
use stdClass;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;

/**
 * Class ApiObjectSerializingTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ApiObjectSerializing::class)]
class ApiObjectSerializingTest extends TestCase
{
    private ApiObjectSerializing $objectSerializing;

    private SerializerInterface&Stub $serializer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->serializer = $this->createStub(SerializerInterface::class);

        $this->objectSerializing = new ApiObjectSerializing(
            $this->serializer,
        );
    }

    public function testGetName(): void
    {
        $this->assertIsString(
            $this->objectSerializing->serialize(new \stdClass()),
        );
    }

    private function createIdentifiedObject(): IdentifiedObjectInterface
    {
        return new class extends stdClass implements IdentifiedObjectInterface {
            public function getId(): string
            {
                return 'object-id';
            }
        };
    }

    public function testSerializeAnIdentifiedObject(): void
    {
        $object = $this->createIdentifiedObject();

        $this->serializer
            ->method('serialize')
            ->willReturnCallback(
                fn (mixed $data): string => json_encode($data['meta'], JSON_THROW_ON_ERROR)
            );

        $this->assertSame(
            '{"id":"object-id","@class":"stdClass","foo":"bar"}',
            $this->objectSerializing->serialize($object, meta: ['foo' => 'bar']),
        );
    }

    public function testSerializeAnArrayWithAParentObject(): void
    {
        $this->serializer
            ->method('serialize')
            ->willReturnCallback(
                fn (mixed $data): string => json_encode($data, JSON_THROW_ON_ERROR)
            );

        $this->assertSame(
            '{"meta":{"id":"object-id","@class":"stdClass"},"data":{"a":1}}',
            $this->objectSerializing->serialize(['a' => 1], parentObject: $this->createIdentifiedObject()),
        );
    }
}
