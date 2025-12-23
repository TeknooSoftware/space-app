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
 */
#[CoversClass(Contact::class)]
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

        $user = $this->createStub(User::class);
        $user->method('getFirstName')->willReturn('foo1');
        $user->method('getLastName')->willReturn('bar1');
        $user->method('getEmail')->willReturn('foo1@bar');

        $this->contact1 = new Contact(null, 'foo', 'foo@bar', 'sfoo', 'mbar');
        $this->contact2 = new Contact(new SpaceUser($user), 'foo', 'foo@bar', 'sfoo', 'mbar');
    }

    public function testConstruct(): void
    {
        $this->assertEquals('foo', $this->contact1->fromName);
        $this->assertEquals('foo@bar', $this->contact1->fromEmail);
        $this->assertEquals('sfoo', $this->contact1->subject);
        $this->assertEquals('mbar', $this->contact1->message);

        $this->assertEquals('foo1 bar1', $this->contact2->fromName);
        $this->assertEquals('foo1@bar', $this->contact2->fromEmail);
        $this->assertEquals('sfoo', $this->contact2->subject);
        $this->assertEquals('mbar', $this->contact2->message);
    }
}
