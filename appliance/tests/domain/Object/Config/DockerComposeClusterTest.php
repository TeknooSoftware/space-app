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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Paas\Object\ClusterCredentials;
use Teknoo\Space\Object\Config\ConfigClusterInterface;
use Teknoo\Space\Object\Config\DockerComposeCluster;

/**
 * Class DockerComposeClusterTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(DockerComposeCluster::class)]
class DockerComposeClusterTest extends TestCase
{
    private const string SSH_KEY = "-----BEGIN OPENSSH PRIVATE KEY-----\nFAKEKEY\n-----END OPENSSH PRIVATE KEY-----";

    private const string KNOWN_HOSTS = 'host.example.com ssh-ed25519 AAAAFAKEHOSTKEY';

    private function buildCluster(string $username = ''): DockerComposeCluster
    {
        return new DockerComposeCluster(
            name: 'Docker Host',
            sluggyName: 'docker-host',
            type: 'docker-compose',
            masterAddress: 'ssh://deployer@host.example.com:22',
            dashboardAddress: 'https://dashboard.example.com',
            isExternal: false,
            clientKey: self::SSH_KEY,
            username: $username,
            caCertificate: self::KNOWN_HOSTS,
        );
    }

    public function testItImplementsConfigClusterInterface(): void
    {
        $this->assertInstanceOf(ConfigClusterInterface::class, $this->buildCluster());
    }

    public function testInterfaceGetters(): void
    {
        $cluster = $this->buildCluster();

        $this->assertSame('Docker Host', $cluster->name);
        $this->assertSame('docker-host', $cluster->sluggyName);
        $this->assertSame('docker-compose', $cluster->type);
        $this->assertSame('ssh://deployer@host.example.com:22', $cluster->masterAddress);
        $this->assertSame('https://dashboard.example.com', $cluster->dashboardAddress);
        $this->assertFalse($cluster->isExternal);
    }

    public function testSupportRegistryDefaultsTrueAndUseHncIsAlwaysFalse(): void
    {
        $cluster = $this->buildCluster();

        $this->assertTrue($cluster->supportRegistry);
        $this->assertFalse($cluster->useHnc);
    }

    public function testGetCredentialsCarriesKeyAndKnownHostsWithoutPassword(): void
    {
        $credentials = $this->buildCluster()->getCredentials();

        $this->assertInstanceOf(ClusterCredentials::class, $credentials);
        $this->assertSame(self::SSH_KEY, $credentials->getClientKey());
        $this->assertSame(self::KNOWN_HOSTS, $credentials->getCaCertificate());
        $this->assertSame('', $credentials->getPassword());
    }

    public function testGetCredentialsUsernameEmptyReliesOnAddress(): void
    {
        $credentials = $this->buildCluster()->getCredentials();

        $this->assertSame('', $credentials->getUsername());
    }

    public function testGetCredentialsUsernameCarriedThroughWhenProvided(): void
    {
        $credentials = $this->buildCluster(username: 'deployer')->getCredentials();

        $this->assertSame('deployer', $credentials->getUsername());
    }

    public function testItHasNoKubernetesClientMethod(): void
    {
        $this->assertFalse(method_exists(DockerComposeCluster::class, 'getKubernetesClient'));
        $this->assertFalse(method_exists(DockerComposeCluster::class, 'getKubernetesRegistryClient'));
    }
}
