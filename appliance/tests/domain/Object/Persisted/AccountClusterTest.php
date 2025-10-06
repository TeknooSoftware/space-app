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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Service\FindSlugService;
use Teknoo\East\Foundation\Normalizer\EastNormalizerInterface;
use Teknoo\East\Paas\Infrastructures\Kubernetes\Contracts\ClientFactoryInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Kubernetes\RepositoryRegistry;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Space\Object\Config\Cluster;
use Teknoo\Space\Object\Persisted\AccountCluster;

/**
 * Class AccountClusterTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AccountCluster::class)]
class AccountClusterTest extends TestCase
{
    private AccountCluster $accountCluster;

    private Account|MockObject $account;

    private string $name;

    private string $slug;

    private string $type;

    private string $masterAddress;

    private string $storageProvisioner;

    private string $dashboardAddress;

    private string $caCertificate;

    private string $token;

    private bool $supportRegistry;

    private ?string $registryUrl;

    private bool $useHnc;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->account = $this->createMock(Account::class);
        $this->name = '42';
        $this->slug = '42';
        $this->type = '42';
        $this->masterAddress = '42';
        $this->storageProvisioner = '42';
        $this->dashboardAddress = '42';
        $this->caCertificate = '42';
        $this->token = '42';
        $this->supportRegistry = true;
        $this->registryUrl = '42';
        $this->useHnc = false;
        $this->accountCluster = new AccountCluster(
            $this->account,
            $this->name,
            $this->slug,
            $this->type,
            $this->masterAddress,
            $this->storageProvisioner,
            $this->dashboardAddress,
            $this->caCertificate,
            $this->token,
            $this->supportRegistry,
            $this->registryUrl,
            $this->useHnc,
        );
    }

    public function testGetAccount(): void
    {
        $this->assertInstanceOf(Account::class, $this->accountCluster->getAccount());
    }

    public function testSetAccount(): void
    {
        $newAccount = $this->createMock(Account::class);
        $result = $this->accountCluster->setAccount($newAccount);

        $this->assertInstanceOf(AccountCluster::class, $result);
        $this->assertSame($newAccount, $this->accountCluster->getAccount());
    }

    public function testSetCaCertificate(): void
    {
        $newCert = 'newCertificate';
        $result = $this->accountCluster->setCaCertificate($newCert);

        $this->assertInstanceOf(AccountCluster::class, $result);
    }

    public function testSetDashboardAddress(): void
    {
        $newAddress = 'https://dashboard.example.com';
        $result = $this->accountCluster->setDashboardAddress($newAddress);

        $this->assertInstanceOf(AccountCluster::class, $result);
    }

    public function testSetMasterAddress(): void
    {
        $newAddress = 'https://master.example.com';
        $result = $this->accountCluster->setMasterAddress($newAddress);

        $this->assertInstanceOf(AccountCluster::class, $result);
    }

    public function testSetName(): void
    {
        $newName = 'New Cluster Name';
        $result = $this->accountCluster->setName($newName);

        $this->assertInstanceOf(AccountCluster::class, $result);
        $this->assertEquals($newName, (string) $this->accountCluster);
    }

    public function testToString(): void
    {
        $this->assertEquals($this->name, (string) $this->accountCluster);
    }

    public function testSetRegistryUrl(): void
    {
        $newUrl = 'https://registry.example.com';
        $result = $this->accountCluster->setRegistryUrl($newUrl);

        $this->assertInstanceOf(AccountCluster::class, $result);
    }

    public function testSetRegistryUrlWithNull(): void
    {
        $result = $this->accountCluster->setRegistryUrl(null);

        $this->assertInstanceOf(AccountCluster::class, $result);
    }

    public function testSetStorageProvisioner(): void
    {
        $newProvisioner = 'kubernetes.io/aws-ebs';
        $result = $this->accountCluster->setStorageProvisioner($newProvisioner);

        $this->assertInstanceOf(AccountCluster::class, $result);
    }

    public function testSetSupportRegistry(): void
    {
        $result = $this->accountCluster->setSupportRegistry(false);

        $this->assertInstanceOf(AccountCluster::class, $result);
    }

    public function testSetToken(): void
    {
        $newToken = 'newToken123';
        $result = $this->accountCluster->setToken($newToken);

        $this->assertInstanceOf(AccountCluster::class, $result);
    }

    public function testSetTokenWithEmptyString(): void
    {
        $result = $this->accountCluster->setToken('');

        $this->assertInstanceOf(AccountCluster::class, $result);
    }

    public function testSetTokenWithNull(): void
    {
        $result = $this->accountCluster->setToken(null);

        $this->assertInstanceOf(AccountCluster::class, $result);
    }

    public function testSetType(): void
    {
        $newType = 'production';
        $result = $this->accountCluster->setType($newType);

        $this->assertInstanceOf(AccountCluster::class, $result);
    }

    public function testSetUseHnc(): void
    {
        $result = $this->accountCluster->setUseHnc(true);

        $this->assertInstanceOf(AccountCluster::class, $result);
    }

    public function testPrepareSlugNearWithExistingSlug(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        $findSlugService = $this->createMock(FindSlugService::class);

        $findSlugService->expects($this->once())
            ->method('process')
            ->with(
                $loader,
                'slug',
                $this->accountCluster,
                [$this->slug]
            );

        $result = $this->accountCluster->prepareSlugNear($loader, $findSlugService, 'slug');

        $this->assertInstanceOf(AccountCluster::class, $result);
    }

    public function testPrepareSlugNearWithEmptySlug(): void
    {
        $accountCluster = new AccountCluster(
            $this->account,
            'Test Name',
            '',
            $this->type,
            $this->masterAddress,
            $this->storageProvisioner,
            $this->dashboardAddress,
            $this->caCertificate,
            $this->token,
            $this->supportRegistry,
            $this->registryUrl,
            $this->useHnc,
        );

        $loader = $this->createMock(LoaderInterface::class);
        $findSlugService = $this->createMock(FindSlugService::class);

        $findSlugService->expects($this->once())
            ->method('process')
            ->with(
                $loader,
                'slug',
                $accountCluster,
                ['Test Name']
            );

        $result = $accountCluster->prepareSlugNear($loader, $findSlugService, 'slug');

        $this->assertInstanceOf(AccountCluster::class, $result);
    }

    public function testSetSlug(): void
    {
        $newSlug = 'new-slug';
        $result = $this->accountCluster->setSlug($newSlug);

        $this->assertInstanceOf(AccountCluster::class, $result);
    }

    public function testConvertToConfigCluster(): void
    {
        $clientFactory = $this->createMock(ClientFactoryInterface::class);
        $repositoryRegistry = $this->createMock(RepositoryRegistry::class);

        $result = $this->accountCluster->convertToConfigCluster($clientFactory, $repositoryRegistry);

        $this->assertInstanceOf(Cluster::class, $result);
    }

    public function testExportToMeData(): void
    {
        $normalizer = $this->createMock(EastNormalizerInterface::class);

        $normalizer->expects($this->once())
            ->method('injectData')
            ->with($this->isArray());

        $result = $this->accountCluster->exportToMeData($normalizer, []);

        $this->assertInstanceOf(AccountCluster::class, $result);
    }

    public function testExportToMeDataWithGroups(): void
    {
        $normalizer = $this->createMock(EastNormalizerInterface::class);

        $normalizer->expects($this->once())
            ->method('injectData')
            ->with($this->isArray());

        $result = $this->accountCluster->exportToMeData($normalizer, ['groups' => ['crud']]);

        $this->assertInstanceOf(AccountCluster::class, $result);
    }

    public function testVerifyAccessToUser(): void
    {
        $user = $this->createMock(User::class);
        $promise = $this->createMock(PromiseInterface::class);

        $this->account->expects($this->once())
            ->method('__call')
            ->with('verifyAccessToUser');

        $result = $this->accountCluster->verifyAccessToUser($user, $promise);

        $this->assertInstanceOf(AccountCluster::class, $result);
    }

    public function testVisit(): void
    {
        $masterAddressValue = null;
        $storageProvisionerValue = null;
        $dashboardAddressValue = null;
        $caCertificateValue = null;
        $supportRegistryValue = null;
        $registryUrlValue = null;
        $nameValue = null;

        $result = $this->accountCluster->visit([
            'master_address' => function ($value) use (&$masterAddressValue): void {
                $masterAddressValue = $value;
            },
            'storageProvisioner' => function ($value) use (&$storageProvisionerValue): void {
                $storageProvisionerValue = $value;
            },
            'dashboard_address' => function ($value) use (&$dashboardAddressValue): void {
                $dashboardAddressValue = $value;
            },
            'ca_certificate' => function ($value) use (&$caCertificateValue): void {
                $caCertificateValue = $value;
            },
            'support_registry' => function ($value) use (&$supportRegistryValue): void {
                $supportRegistryValue = $value;
            },
            'registry_url' => function ($value) use (&$registryUrlValue): void {
                $registryUrlValue = $value;
            },
            'name' => function ($value) use (&$nameValue): void {
                $nameValue = $value;
            },
            'foo' => fn () => self::fail('Must not be called'),
        ]);

        $this->assertInstanceOf(AccountCluster::class, $result);
        $this->assertEquals($this->masterAddress, $masterAddressValue);
        $this->assertEquals($this->storageProvisioner, $storageProvisionerValue);
        $this->assertEquals($this->dashboardAddress, $dashboardAddressValue);
        $this->assertEquals($this->caCertificate, $caCertificateValue);
        $this->assertEquals($this->supportRegistry, $supportRegistryValue);
        $this->assertEquals($this->registryUrl, $registryUrlValue);
        $this->assertEquals($this->name, $nameValue);
    }

    public function testVisitWithSnakeCaseMapping(): void
    {
        $masterAddressValue = null;
        $dashboardAddressValue = null;
        $caCertificateValue = null;
        $supportRegistryValue = null;
        $registryUrlValue = null;

        $result = $this->accountCluster->visit([
            'master_address' => function ($value) use (&$masterAddressValue): void {
                $masterAddressValue = $value;
            },
            'dashboard_address' => function ($value) use (&$dashboardAddressValue): void {
                $dashboardAddressValue = $value;
            },
            'ca_certificate' => function ($value) use (&$caCertificateValue): void {
                $caCertificateValue = $value;
            },
            'support_registry' => function ($value) use (&$supportRegistryValue): void {
                $supportRegistryValue = $value;
            },
            'registry_url' => function ($value) use (&$registryUrlValue): void {
                $registryUrlValue = $value;
            },
        ]);

        $this->assertInstanceOf(AccountCluster::class, $result);
        $this->assertEquals($this->masterAddress, $masterAddressValue);
        $this->assertEquals($this->dashboardAddress, $dashboardAddressValue);
        $this->assertEquals($this->caCertificate, $caCertificateValue);
        $this->assertEquals($this->supportRegistry, $supportRegistryValue);
        $this->assertEquals($this->registryUrl, $registryUrlValue);
    }
}
