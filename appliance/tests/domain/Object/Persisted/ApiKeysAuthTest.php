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

namespace Teknoo\Space\Tests\Unit\Object\Persisted;

use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\Space\Object\Persisted\ApiKeyToken;
use Teknoo\Space\Object\Persisted\ApiKeysAuth;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ApiKeysAuth::class)]
class ApiKeysAuthTest extends TestCase
{
    private function createToken(string $name): ApiKeyToken&Stub
    {
        $t = $this->createStub(ApiKeyToken::class);
        $t->method('getName')->willReturn($name);

        return $t;
    }

    public function testGetTokens(): void
    {
        $a = new ApiKeysAuth(tokens: [$this->createToken('a'), $this->createToken('b')]);

        $this->assertCount(2, [...$a->getTokens()]);
    }

    public function testGetTokenFound(): void
    {
        $a = new ApiKeysAuth(tokens: [$this->createToken('x'), $this->createToken('y')]);

        $found = $a->getToken('y');

        $this->assertInstanceOf(ApiKeyToken::class, $found);
        $this->assertSame('y', $found?->getName());
    }

    public function testGetTokenNotFound(): void
    {
        $a = new ApiKeysAuth(tokens: [$this->createToken('x'), $this->createToken('y')]);

        $this->assertNull($a->getToken('z'));
    }

    public function testAddTokenDuplicateThrows(): void
    {
        $existing = $this->createToken('dup');
        $a = new ApiKeysAuth(tokens: [$existing]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('teknoo.space.error.space_user.api_key_already_exists');
        $this->expectExceptionCode(400);

        $a->addToken($this->createToken('dup'));
    }

    public function testAddTokenNotAvailable(): void
    {
        $a = new ApiKeysAuth(tokens: new \MultipleIterator());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('teknoo.space.error.space_user.api_key.list_not_accessible');
        $this->expectExceptionCode(500);

        $a->addToken($this->createToken('dup'));
    }

    public function testAddTokenNoDuplicateReturnsSelf(): void
    {
        $a = new ApiKeysAuth(tokens: [$this->createToken('a')]);

        $result = $a->addToken($this->createToken('b'));

        $this->assertSame($a, $result);
    }

    public function testRemoveToken(): void
    {
        $t1 = $this->createToken('a');
        $t2 = $this->createToken('b');
        $a = new ApiKeysAuth(tokens: [$t1, $t2]);

        $a->removeToken('a');
        $remaining = [...$a->getTokens()];

        $this->assertCount(1, $remaining);
        $this->assertSame('b', $remaining[0]->getName());
    }
}
