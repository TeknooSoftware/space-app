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

namespace Teknoo\Space\Tests\Unit\Object\DTO;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\Space\Object\DTO\SpaceAccount;
use Teknoo\Space\Object\DTO\SpaceSubscription;
use Teknoo\Space\Object\DTO\SpaceUser;

/**
 * Class SpaceSubscriptionTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SpaceSubscription::class)]
class SpaceSubscriptionTest extends TestCase
{
    private SpaceSubscription $spaceSubscription;

    private SpaceUser&Stub $user;

    private SpaceAccount&Stub $account;

    private string $code;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createStub(SpaceUser::class);
        $this->account = $this->createStub(SpaceAccount::class);
        $this->code = '42';
        $this->spaceSubscription = new SpaceSubscription($this->user, $this->account, $this->code);
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            SpaceSubscription::class,
            $this->spaceSubscription,
        );
    }
}
