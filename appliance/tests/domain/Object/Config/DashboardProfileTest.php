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

namespace Teknoo\Space\Tests\Unit\Object\Config;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Teknoo\Space\Object\Config\DashboardProfile;

use function str_repeat;

/**
 * Class DashboardProfileTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(DashboardProfile::class)]
class DashboardProfileTest extends TestCase
{
    private function createProfile(): DashboardProfile
    {
        return new DashboardProfile(
            name: 'foo',
            requestHeaders: ['Authorization' => 'Bearer {token}', 'X-Space' => 'static'],
            entryPath: 'c/main',
            namespacedEntryPath: 'c/main/workloads?namespace={namespace}',
            headSnippet: '<base href="{baseHref}">',
            namespacedHeadSnippet: '<base href="{baseHref}"><script>var ns = \'{namespace}\';</script>',
            rewriteBasePath: true,
            pathAliases: ['config/config.json' => 'assets/config/config.json'],
        );
    }

    public function testDefaultValues(): void
    {
        $profile = new DashboardProfile(name: 'foo', requestHeaders: []);

        $this->assertSame('foo', $profile->name);
        $this->assertSame('', $profile->renderEntryPath(null));
        $this->assertSame('', $profile->renderEntryPath('space-ns'));
        $this->assertSame('', $profile->renderHeadSnippet('https://space.test/', null));
        $this->assertFalse($profile->rewriteBasePath);
        $this->assertSame([], $profile->renderRequestHeaders('a-token'));
    }

    public function testRenderRequestHeaders(): void
    {
        $this->assertSame(
            ['Authorization' => 'Bearer a-token', 'X-Space' => 'static'],
            $this->createProfile()->renderRequestHeaders('a-token'),
        );
    }

    public function testRenderEntryPathForAllNamespaces(): void
    {
        $this->assertSame('c/main', $this->createProfile()->renderEntryPath(null));
    }

    public function testRenderEntryPathForANamespace(): void
    {
        $this->assertSame(
            'c/main/workloads?namespace=space-client-foo-prod',
            $this->createProfile()->renderEntryPath('space-client-foo-prod'),
        );
    }

    public function testRenderHeadSnippetForAllNamespaces(): void
    {
        $this->assertSame(
            '<base href="https://space.test/frame/?a=1&amp;b=&quot;2&quot;">',
            $this->createProfile()->renderHeadSnippet('https://space.test/frame/?a=1&b="2"', null),
        );
    }

    public function testRenderHeadSnippetForANamespace(): void
    {
        $this->assertSame(
            '<base href="https://space.test/frame/"><script>var ns = \'space-ns\';</script>',
            $this->createProfile()->renderHeadSnippet('https://space.test/frame/', 'space-ns'),
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidNamespacesProvider(): iterable
    {
        yield 'quote' => ["foo'bar"];
        yield 'html' => ['foo<script>'];
        yield 'uppercase' => ['Foo'];
        yield 'leading dash' => ['-foo'];
        yield 'empty' => [''];
        yield 'too long' => [str_repeat('a', 64)];
    }

    #[DataProvider('invalidNamespacesProvider')]
    public function testRenderEntryPathRefusesAnInvalidNamespace(string $namespace): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->createProfile()->renderEntryPath($namespace);
    }

    #[DataProvider('invalidNamespacesProvider')]
    public function testRenderHeadSnippetRefusesAnInvalidNamespace(string $namespace): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->createProfile()->renderHeadSnippet('https://space.test/frame/', $namespace);
    }

    public function testResolvePath(): void
    {
        $profile = $this->createProfile();

        $this->assertSame('assets/config/config.json', $profile->resolvePath('config/config.json'));
        $this->assertSame('api/v1/pods', $profile->resolvePath('api/v1/pods'));
    }
}
