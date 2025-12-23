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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Misc;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Query\Expr\ObjectReference;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Recipe\Step\Misc\PrepareCriteria;
use Teknoo\Space\Recipe\Step\Project\Exception\RuntimeException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(PrepareCriteria::class)]
class PrepareCriteriaTest extends TestCase
{
    private PrepareCriteria $prepareCriteria;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareCriteria = new PrepareCriteria();
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            PrepareCriteria::class,
            ($this->prepareCriteria)(
                $this->createStub(ManagerInterface::class),
                $this->createStub(Account::class),
                [],
            ),
        );
    }

    public function testInvokeWithoutAccount(): void
    {
        $this->expectException(RuntimeException::class);

        ($this->prepareCriteria)(
            $this->createStub(ManagerInterface::class),
            null,
            [],
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInvokeWithEmptyCriteria(): void
    {
        $account = $this->createMock(Account::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) use ($account) {
                    return isset($workplan['criteria'])
                        && is_array($workplan['criteria'])
                        && isset($workplan['criteria']['account'])
                        && $workplan['criteria']['account'] instanceof ObjectReference
                        && 1 === count($workplan['criteria']);
                })
            );

        $result = ($this->prepareCriteria)(
            $manager,
            $account,
            [],
        );

        $this->assertInstanceOf(PrepareCriteria::class, $result);
    }

    public function testInvokeWithExistingCriteria(): void
    {
        $account = $this->createStub(Account::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) use ($account) {
                    return isset($workplan['criteria'])
                        && is_array($workplan['criteria'])
                        && isset($workplan['criteria']['account'])
                        && $workplan['criteria']['account'] instanceof ObjectReference
                        && isset($workplan['criteria']['status'])
                        && 'active' === $workplan['criteria']['status']
                        && isset($workplan['criteria']['name'])
                        && 'test-name' === $workplan['criteria']['name']
                        && 3 === count($workplan['criteria']);
                })
            );

        $result = ($this->prepareCriteria)(
            $manager,
            $account,
            ['status' => 'active', 'name' => 'test-name'],
        );

        $this->assertInstanceOf(PrepareCriteria::class, $result);
    }

    public function testInvokeWithCriteriaContainingAccount(): void
    {
        $account = $this->createStub(Account::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) use ($account) {
                    // The account from criteria should be overridden by ObjectReference
                    return isset($workplan['criteria'])
                        && is_array($workplan['criteria'])
                        && isset($workplan['criteria']['account'])
                        && $workplan['criteria']['account'] instanceof ObjectReference
                        && 1 === count($workplan['criteria']);
                })
            );

        $result = ($this->prepareCriteria)(
            $manager,
            $account,
            ['account' => 'old-account-value'],
        );

        $this->assertInstanceOf(PrepareCriteria::class, $result);
    }

    public function testInvokeWithNullAccountThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('teknoo.space.error.space_account.account.fetching');
        $this->expectExceptionCode(403);

        ($this->prepareCriteria)(
            $this->createStub(ManagerInterface::class),
            null,
        );
    }
}
