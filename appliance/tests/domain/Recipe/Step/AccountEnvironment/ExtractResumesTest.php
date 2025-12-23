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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\AccountEnvironment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Recipe\Step\AccountEnvironment\ExtractResumes;

use function array_key_exists;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ExtractResumes::class)]
class ExtractResumesTest extends TestCase
{
    private ExtractResumes $extractResumes;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->extractResumes = new ExtractResumes();
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            ExtractResumes::class,
            ($this->extractResumes)(
                $this->createStub(ManagerInterface::class),
                new SpaceAccount(
                    account: $this->createStub(Account::class),
                    environments: []
                ),
            ),
        );
    }

    public function testInvokeWithUpdateWorkPlan(): void
    {
        $environments = [(object)['id' => '1'], (object)['id' => '2']];

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) use ($environments) {
                    return isset($workplan['accountEnvsResumes'])
                        && $workplan['accountEnvsResumes'] === $environments;
                })
            );

        $this->assertInstanceOf(
            ExtractResumes::class,
            ($this->extractResumes)(
                $manager,
                new SpaceAccount(
                    account: $this->createStub(Account::class),
                    environments: $environments
                ),
            ),
        );
    }

    public function testInvokeWithNullEnvironments(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return array_key_exists('accountEnvsResumes', $workplan)
                        && null === $workplan['accountEnvsResumes'];
                })
            );

        $this->assertInstanceOf(
            ExtractResumes::class,
            ($this->extractResumes)(
                $manager,
                new SpaceAccount(
                    account: $this->createStub(Account::class),
                    environments: null
                ),
            ),
        );
    }

    public function testInvokeWithEmptyEnvironments(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan['accountEnvsResumes'])
                        && [] === $workplan['accountEnvsResumes'];
                })
            );

        $this->assertInstanceOf(
            ExtractResumes::class,
            ($this->extractResumes)(
                $manager,
                new SpaceAccount(
                    account: $this->createStub(Account::class),
                    environments: []
                ),
            ),
        );
    }
}
