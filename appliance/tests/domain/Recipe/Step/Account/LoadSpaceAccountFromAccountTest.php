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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Loader\Meta\SpaceAccountLoader;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Recipe\Step\Account\LoadSpaceAccountFromAccount;

/**
 * Class LoadSpaceAccountFromAccountTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(LoadSpaceAccountFromAccount::class)]
class LoadSpaceAccountFromAccountTest extends TestCase
{
    private LoadSpaceAccountFromAccount $loadSpaceAccountFromAccount;

    private SpaceAccountLoader&MockObject $spaceAccountLoader;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->spaceAccountLoader = $this->createMock(SpaceAccountLoader::class);

        $this->loadSpaceAccountFromAccount = new LoadSpaceAccountFromAccount($this->spaceAccountLoader);
    }

    public function testInvoke(): void
    {
        $account = $this->createMock(Account::class);
        $account->method('getId')->willReturn('fooo');

        $user = $this->createMock(User::class);
        $user->method('getRoles')->willReturn(['ROLE_ADMIN']);

        $spaceAccount = new SpaceAccount($account);

        $this->assertInstanceOf(
            LoadSpaceAccountFromAccount::class,
            ($this->loadSpaceAccountFromAccount)(
                $this->createMock(ManagerInterface::class),
                $account,
                $spaceAccount,
                $user,
            )
        );
    }
}
