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

namespace Teknoo\Space\Tests\Unit\Object\DTO;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\User;
use Teknoo\Space\Object\DTO\Contact;
use Teknoo\Space\Object\DTO\SpaceUser;

/**
 * Class SearchTest.
 *
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Space\Object\DTO\Contact
 */
class ContactTest extends TestCase
{
    private Contact $contact1;

    private Contact $contact2;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $user = $this->createMock(User::class);
        $user->expects($this->any())->method('getFirstName')->willReturn('foo1');
        $user->expects($this->any())->method('getLastName')->willReturn('bar1');
        $user->expects($this->any())->method('getEmail')->willReturn('foo1@bar');

        $this->contact1 = new Contact(null, 'foo', 'foo@bar', 'sfoo', 'mbar');
        $this->contact2 = new Contact(new SpaceUser($user), 'foo', 'foo@bar', 'sfoo', 'mbar');
    }

    public function testConstruct()
    {
        self::assertEquals('foo', $this->contact1->fromName);
        self::assertEquals('foo@bar', $this->contact1->fromEmail);
        self::assertEquals('sfoo', $this->contact1->subject);
        self::assertEquals('mbar', $this->contact1->message);

        self::assertEquals('foo1 bar1', $this->contact2->fromName);
        self::assertEquals('foo1@bar', $this->contact2->fromEmail);
        self::assertEquals('sfoo', $this->contact2->subject);
        self::assertEquals('mbar', $this->contact2->message);
    }
}
