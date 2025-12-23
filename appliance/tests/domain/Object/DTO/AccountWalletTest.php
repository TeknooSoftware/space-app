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

namespace Teknoo\Space\Tests\Unit\Object\DTO;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Paas\Object\Cluster;
use Teknoo\East\Paas\Object\Environment;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\Persisted\AccountEnvironment;

/**
 * Class AccountWalletTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountWallet::class)]
class AccountWalletTest extends TestCase
{
    private AccountWallet $accountWallet;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->accountWallet = new AccountWallet([$this->createStub(AccountEnvironment::class)]);
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            AccountWallet::class,
            $this->accountWallet,
        );
    }

    public function testGetIterator(): void
    {
        $env1 = $this->createStub(AccountEnvironment::class);
        $env2 = $this->createStub(AccountEnvironment::class);

        $wallet = new AccountWallet([$env1, $env2]);

        $result = iterator_to_array($wallet);
        $this->assertCount(2, $result);
        $this->assertSame($env1, $result[0]);
        $this->assertSame($env2, $result[1]);
    }

    public function testGetWithStringFound(): void
    {
        $env = $this->createStub(AccountEnvironment::class);
        $env
            ->method('getClusterName')
            ->willReturn('cluster1');
        $env
            ->method('getEnvName')
            ->willReturn('prod');

        $wallet = new AccountWallet([$env]);

        $this->assertSame($env, $wallet->get('cluster1', 'prod'));
    }

    public function testGetWithObjectsFound(): void
    {
        $env = $this->createStub(AccountEnvironment::class);
        $env->method('getClusterName')
            ->willReturn('cluster1');
        $env->method('getEnvName')
            ->willReturn('prod');

        $cluster = $this->createStub(Cluster::class);
        $cluster->method('__toString')
            ->willReturn('cluster1');

        $environment = $this->createStub(Environment::class);
        $environment->method('__toString')
            ->willReturn('prod');

        $wallet = new AccountWallet([$env]);

        $this->assertSame($env, $wallet->get($cluster, $environment));
    }

    public function testGetNotFound(): void
    {
        $env = $this->createStub(AccountEnvironment::class);
        $env->method('getClusterName')
            ->willReturn('cluster1');
        $env->method('getEnvName')
            ->willReturn('prod');

        $wallet = new AccountWallet([$env]);

        $this->assertNull($wallet->get('cluster2', 'dev'));
    }

    public function testHasFound(): void
    {
        $env = $this->createStub(AccountEnvironment::class);
        $env->method('getClusterName')
            ->willReturn('cluster1');
        $env->method('getEnvName')
            ->willReturn('prod');

        $wallet = new AccountWallet([$env]);

        $this->assertTrue($wallet->has('cluster1', 'prod'));
    }

    public function testHasNotFound(): void
    {
        $env = $this->createStub(AccountEnvironment::class);
        $env->method('getClusterName')
            ->willReturn('cluster1');
        $env->method('getEnvName')
            ->willReturn('prod');

        $wallet = new AccountWallet([$env]);

        $this->assertFalse($wallet->has('cluster2', 'dev'));
    }

    public function testHasWithObjects(): void
    {
        $env = $this->createStub(AccountEnvironment::class);
        $env->method('getClusterName')
            ->willReturn('cluster1');
        $env->method('getEnvName')
            ->willReturn('prod');

        $cluster = $this->createStub(Cluster::class);
        $cluster->method('__toString')
            ->willReturn('cluster1');

        $environment = $this->createStub(Environment::class);
        $environment->method('__toString')
            ->willReturn('prod');

        $wallet = new AccountWallet([$env]);

        $this->assertTrue($wallet->has($cluster, $environment));
    }
}
