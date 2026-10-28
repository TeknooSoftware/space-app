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

use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Recipe\Step\Account\UseAccountOfUser;

/**
 * Class UseAccountOfUserTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(UseAccountOfUser::class)]
class UseAccountOfUserTest extends TestCase
{
    private UseAccountOfUser $useAccountOfUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useAccountOfUser = new UseAccountOfUser();
    }

    public function testWithoutTheFlagTheIdOfTheRouteIsKept(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');
        $manager->expects($this->never())->method('error');

        $parametersBag = $this->createMock(ParametersBag::class);
        $parametersBag->expects($this->never())->method('set');

        $this->assertInstanceOf(
            UseAccountOfUser::class,
            ($this->useAccountOfUser)(
                manager: $manager,
                parametersBag: $parametersBag,
                account: $this->createStub(Account::class),
            )
        );
    }

    public function testTheIdIsTheOneOfTheAccountOfTheUser(): void
    {
        $account = $this->createStub(Account::class);
        $account->method('getId')->willReturn('account-123');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->with(['id' => 'account-123']);
        $manager->expects($this->never())->method('error');

        $parametersBag = $this->createMock(ParametersBag::class);
        $parametersBag->expects($this->once())
            ->method('set')
            ->with('accountId', 'account-123');

        $this->assertInstanceOf(
            UseAccountOfUser::class,
            ($this->useAccountOfUser)(
                manager: $manager,
                parametersBag: $parametersBag,
                useAccountOfUser: true,
                account: $account,
            )
        );
    }

    public function testWithoutAccountOfTheUserTheAccessIsDenied(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');
        $manager->expects($this->once())
            ->method('error')
            ->with(
                $this->callback(
                    fn ($error): bool => $error instanceof DomainException && 403 === $error->getCode()
                )
            );

        $parametersBag = $this->createMock(ParametersBag::class);
        $parametersBag->expects($this->never())->method('set');

        $this->assertInstanceOf(
            UseAccountOfUser::class,
            ($this->useAccountOfUser)(
                manager: $manager,
                parametersBag: $parametersBag,
                useAccountOfUser: true,
            )
        );
    }
}
