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
use Teknoo\Space\Object\Persisted\AccountCredential;
use Teknoo\Space\Recipe\Step\Job\JobAddExtra;

/**
 * Class JobAddExtraTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Recipe\Step\Job\JobAddExtra
 */
class JobAddExtraTest extends TestCase
{
    private JobAddExtra $jobAddExtra;

    private string $storageProvisioner;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->storageProvisioner = '42';
        $this->jobAddExtra = new JobAddExtra($this->storageProvisioner);
    }

    public function testInvoke(): void
    {
        self::assertInstanceOf(
            JobAddExtra::class,
            ($this->jobAddExtra)(
                $this->createMock(ManagerInterface::class),
                $this->createMock(Job::class),
                $this->createMock(AccountCredential::class),
            ),
        );
    }
}
