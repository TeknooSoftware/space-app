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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Job;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Job;
use Teknoo\Space\Object\Config\ClusterCatalog;
use Teknoo\Space\Object\DTO\AccountWallet;
use Teknoo\Space\Object\Persisted\AccountCredential;
use Teknoo\Space\Recipe\Step\Job\JobSetDefaults;

/**
 * Class JobSetDefaultsTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Recipe\Step\Job\JobSetDefaults
 */
class JobSetDefaultsTest extends TestCase
{
    private JobSetDefaults $jobSetDefaults;

    private string $storageProvisioner;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->storageProvisioner = '42';
        $this->jobSetDefaults = new JobSetDefaults(
            $this->createMock(ClusterCatalog::class)
        );
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            JobSetDefaults::class,
            ($this->jobSetDefaults)(
                manager: $this->createMock(ManagerInterface::class),
                job: $this->createMock(Job::class),
                accountWallet: $this->createMock(AccountWallet::class),
            ),
        );
    }
}
