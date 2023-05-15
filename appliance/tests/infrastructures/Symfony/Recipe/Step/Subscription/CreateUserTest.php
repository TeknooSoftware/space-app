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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Recipe\Step\Subscription;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Subscription\CreateUser;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\DTO\SpaceSubscription;
use Teknoo\Space\Object\DTO\SpaceUser;
use Teknoo\Space\Writer\Meta\SpaceUserWriter;

/**
 * Class CreateUserTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Subscription\CreateUser
 */
class CreateUserTest extends TestCase
{
    private CreateUser $createUser;

    private SpaceUserWriter|MockObject $userWriter;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userWriter = $this->createMock(SpaceUserWriter::class);
        $this->createUser = new CreateUser($this->userWriter);
    }

    public function testInvoke(): void
    {
        $user = $this->createMock(User::class);
        $subscription = new SpaceSubscription(
            new SpaceUser($user),
            new SpaceAccount($this->createMock(Account::class))
        );
        self::assertInstanceOf(
            CreateUser::class,
            ($this->createUser)(
                $subscription,
                $this->createMock(ManagerInterface::class),
            )
        );
    }
}
