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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Account;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Recipe\Step\Account\InjectToView;

/**
 * Class InjectToViewTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(InjectToView::class)]
class InjectToViewTest extends TestCase
{
    private InjectToView $InjectToView;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();


        $this->InjectToView = new InjectToView();
    }

    public function testInvoke(): void
    {
        $this->assertInstanceOf(
            InjectToView::class,
            ($this->InjectToView)(
                $this->createMock(ManagerInterface::class),
                $this->createMock(ParametersBag::class),
                new SpaceAccount($account = $this->createMock(Account::class)),
                $account,
            )
        );
    }

    public function testInvokeWithSpaceAccountOnly(): void
    {
        $account = $this->createMock(Account::class);
        $account->expects($this->once())
            ->method('getId')
            ->willReturn('account-123');

        $spaceAccount = new SpaceAccount($account);

        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->once())
            ->method('set')
            ->with('spaceAccount', $spaceAccount);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->assertInstanceOf(
            InjectToView::class,
            ($this->InjectToView)(
                manager: $manager,
                bag: $bag,
                spaceAccount: $spaceAccount,
            )
        );
    }

    public function testInvokeWithAccountOnly(): void
    {
        $account = $this->createMock(Account::class);
        $account->expects($this->once())
            ->method('getId')
            ->willReturn('account-456');

        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->once())
            ->method('set')
            ->with('account', $account);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->assertInstanceOf(
            InjectToView::class,
            ($this->InjectToView)(
                manager: $manager,
                bag: $bag,
                account: $account,
            )
        );
    }

    public function testInvokeWithNullAccounts(): void
    {
        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->never())->method('set');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->assertInstanceOf(
            InjectToView::class,
            ($this->InjectToView)(
                manager: $manager,
                bag: $bag,
                spaceAccount: null,
                account: null,
            )
        );
    }

    public function testInvokeWithAllowAccountSelection(): void
    {
        $account = $this->createMock(Account::class);
        $account->expects($this->any())
            ->method('getId')
            ->willReturn('account-789');

        $spaceAccount = new SpaceAccount($account);

        $bag = $this->createMock(ParametersBag::class);
        $bag->expects($this->exactly(2))
            ->method('set')
            ->willReturnCallback(function ($key, $value) use ($spaceAccount, $account, $bag) {
                $this->assertTrue(
                    ('spaceAccount' === $key && $value === $spaceAccount) ||
                    ('account' === $key && $value === $account)
                );
                return $bag;
            });

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan['parameters'])
                        && isset($workplan['parameters']['accountId'])
                        && 'account-789' === $workplan['parameters']['accountId'];
                })
            );

        $this->assertInstanceOf(
            InjectToView::class,
            ($this->InjectToView)(
                manager: $manager,
                bag: $bag,
                spaceAccount: $spaceAccount,
                account: $account,
                allowAccountSelection: true,
            )
        );
    }

    public function testInvokeWithCustomParameters(): void
    {
        $account = $this->createMock(Account::class);
        $account->expects($this->any())
            ->method('getId')
            ->willReturn('account-999');

        $bag = $this->createMock(ParametersBag::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(
                $this->callback(function ($workplan) {
                    return isset($workplan['parameters'])
                        && 'account-999' === $workplan['parameters']['accountId']
                        && 'value' === $workplan['parameters']['custom'];
                })
            );

        $this->assertInstanceOf(
            InjectToView::class,
            ($this->InjectToView)(
                manager: $manager,
                bag: $bag,
                account: $account,
                allowAccountSelection: true,
                parameters: ['custom' => 'value'],
            )
        );
    }
}
