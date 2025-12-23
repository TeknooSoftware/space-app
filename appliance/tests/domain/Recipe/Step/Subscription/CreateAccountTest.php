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

namespace Teknoo\Space\Tests\Unit\Recipe\Step\Subscription;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Paas\Object\Account;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\DTO\SpaceSubscription;
use Teknoo\Space\Object\DTO\SpaceUser;
use Teknoo\Space\Recipe\Step\Subscription\CreateAccount;
use Teknoo\Space\Writer\Meta\SpaceAccountWriter;

/**
 * Class CreateAccountTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(CreateAccount::class)]
class CreateAccountTest extends TestCase
{
    private CreateAccount $createAccount;

    private SpaceAccountWriter&Stub $accountWriter;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->accountWriter = $this->createStub(SpaceAccountWriter::class);
        $this->createAccount = new CreateAccount($this->accountWriter);
    }

    public function testInvoke(): void
    {
        $user = $this->createStub(User::class);
        $subscription = new SpaceSubscription(
            new SpaceUser($user),
            new SpaceAccount($this->createStub(Account::class))
        );
        $this->assertInstanceOf(
            CreateAccount::class,
            ($this->createAccount)(
                $subscription,
                $user,
                $this->createStub(ManagerInterface::class),
            ),
        );
    }
}
