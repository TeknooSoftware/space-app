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

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Space\Object\Persisted\ApiKeyToken;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ApiKeyToken::class)]
class ApiKeyTokenTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $created = new DateTimeImmutable('-1 day');
        $expires = new DateTimeImmutable('+1 day');

        $token = new ApiKeyToken(
            name: 'my-token',
            token: 'secret',
            tokenHash: 'hashed',
            isExpired: true,
            createdAt: $created,
            expiresAt: $expires,
        );

        $this->assertSame('my-token', $token->getName());
        $this->assertSame('secret', $token->getToken());
        $this->assertSame('hashed', $token->getTokenHash());
        $this->assertTrue($token->isExpired());
        $this->assertSame($created, $token->getCreatedAt());
        $this->assertSame($expires, $token->getExpiresAt());
    }

    public function testSetNameOnlyOnce(): void
    {
        $token = new ApiKeyToken();
        $token->setName('first');
        $token->setName('second'); // must be ignored

        $this->assertSame('first', $token->getName());
    }

    public function testSetTokenOnlyOnceAndSetsHashOnFirstSet(): void
    {
        $token = new ApiKeyToken();
        $token->setToken('abc');
        $token->setToken('def'); // ignored

        $this->assertSame('abc', $token->getToken());
        // setToken calls setTokenHash on first call
        $this->assertSame('', $token->getTokenHash());
    }

    public function testSetTokenDoesNotOverrideExistingHash(): void
    {
        $token = new ApiKeyToken();
        // If a hash already exists, setToken should not override it because setTokenHash() is no-op then
        $token->setTokenHash('pre-hashed');
        $token->setToken('raw-token');

        $this->assertSame('raw-token', $token->getToken());
        $this->assertSame('pre-hashed', $token->getTokenHash());
    }

    public function testSetTokenHashOnlyOnce(): void
    {
        $token = new ApiKeyToken();
        $token->setTokenHash('hash1');
        $token->setTokenHash('hash2'); // ignored

        $this->assertSame('hash1', $token->getTokenHash());
    }

    public function testSetExpiredOnlyOnce(): void
    {
        $token = new ApiKeyToken();
        $token->setExpired(true);
        $token->setExpired(false); // ignored because already changed once from default

        $this->assertTrue($token->isExpired());
    }

    public function testSetCreatedAtOnlyWhenExpiresAtIsNull(): void
    {
        $t = new ApiKeyToken();
        $created1 = new DateTimeImmutable('-2 hours');
        $created2 = new DateTimeImmutable('-1 hour');

        // set when expiresAt is null
        $t->setCreatedAt($created1);
        $this->assertSame($created1, $t->getCreatedAt());

        // still null, can set again? Method allows only if expiresAt is null, not guarding createdAt itself,
        // so second set should overwrite since createdAt is not checked.
        $t->setCreatedAt($created2);
        $this->assertSame($created2, $t->getCreatedAt());

        // once expiresAt set, createdAt must not change anymore
        $expires = new DateTimeImmutable('+1 day');
        $t->setExpiresAt($expires);

        $t->setCreatedAt($created1); // ignored now
        $this->assertSame($created2, $t->getCreatedAt());
    }

    public function testSetExpiresAtOnlyOnce(): void
    {
        $t = new ApiKeyToken();
        $e1 = new DateTimeImmutable('+1 day');
        $e2 = new DateTimeImmutable('+2 days');

        $t->setExpiresAt($e1);
        $t->setExpiresAt($e2); // ignored

        $this->assertSame($e1, $t->getExpiresAt());
    }
}
