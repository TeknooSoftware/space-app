<?php

/*
 * Teknoo Space.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.space Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Recipe\Step\AccountEnvironment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Kubernetes\Client;
use Teknoo\Space\Object\Config\Cluster;
use Teknoo\Space\Object\Config\Cluster as ClusterConfig;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\Persisted\AccountHistory;
use Teknoo\Space\Recipe\Step\AccountEnvironment\PersistEnvironment;
use Teknoo\Space\Writer\AccountEnvironmentWriter;

/**
 * Class PersistCredentialsTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(PersistEnvironment::class)]
class PersistEnvironmentTest extends TestCase
{
    private PersistEnvironment $persistCredentials;

    private AccountEnvironmentWriter|MockObject $writer;

    private DatesService|MockObject $datesService;

    private string $clusterName;

    private bool $preferRealDate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->writer = $this->createMock(AccountEnvironmentWriter::class);
        $this->datesService = $this->createMock(DatesService::class);
        $this->clusterName = '42';
        $this->preferRealDate = true;
        $this->persistCredentials = new PersistEnvironment(
            $this->writer,
            $this->datesService,
            $this->preferRealDate,
        );
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            PersistEnvironment::class,
            actual: ($this->persistCredentials)(
                manager: $this->createMock(ManagerInterface::class),
                spaceAccount: new SpaceAccount($this->createMock(Account::class)),
                envName: 'foo',
                kubeNamespace: 'foo',
                serviceName: 'foo',
                roleName: 'foo',
                roleBindingName: 'foo',
                caCertificate: 'foo',
                token: 'foo',
                accountHistory: $this->createMock(AccountHistory::class),
                clusterConfig: new ClusterConfig(
                    name: 'foo',
                    sluggyName: 'bar',
                    type: 'foo',
                    masterAddress: 'foo',
                    storageProvisioner: 'foo',
                    dashboardAddress: 'foo',
                    kubernetesClient: $this->createMock(Client::class),
                    token: 'foo',
                    supportRegistry: true,
                    useHnc: true,
                ),
                envMetadata: ['foo' => 'bar'],
            ),
        );
    }
}
