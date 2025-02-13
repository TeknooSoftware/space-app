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
 * @link        http://https://teknoo.software/applications/space Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Tests\Unit\Object\Persisted;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Teknoo\East\Paas\Object\Account;
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
        self::assertInstanceOf(Account::class, $this->accountCluster->getAccount());
    }
}
