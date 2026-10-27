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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Dashboard;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Recipe\Step\Dashboard\RenderWithoutAccount;

/**
 * Class RenderWithoutAccountTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(RenderWithoutAccount::class)]
class RenderWithoutAccountTest extends TestCase
{
    private function buildUser(string $role): User
    {
        $user = new User();
        $user->setRoles([$role]);

        return $user;
    }

    public function testInvokeForAnAdministrator(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('continue');

        $this->assertInstanceOf(
            RenderWithoutAccount::class,
            (new RenderWithoutAccount('foo.html.twig'))($manager, $this->buildUser('ROLE_ADMIN')),
        );
    }

    public function testInvokeForAUserWithAnAccount(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('continue');

        $this->assertInstanceOf(
            RenderWithoutAccount::class,
            (new RenderWithoutAccount('foo.html.twig'))(
                $manager,
                $this->buildUser('ROLE_USER'),
                $this->createStub(Account::class),
            ),
        );
    }

    public function testInvokeForAUserWithoutAccount(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('continue')
            ->with(['template' => 'foo.html.twig'], Render::class)
            ->willReturnSelf();

        $this->assertInstanceOf(
            RenderWithoutAccount::class,
            (new RenderWithoutAccount('foo.html.twig'))($manager, $this->buildUser('ROLE_USER')),
        );
    }
}
