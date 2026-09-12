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

namespace Teknoo\Space\Tests\Unit\Infrastructures\Symfony\Recipe\Step\Email;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Email\Exception\BotForbidden;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Email\Exception\InvalidArgumentException;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Email\SendEmail;
use Teknoo\Space\Object\DTO\Contact;
use Teknoo\Space\Object\DTO\ContactAttachment;

/**
 * Class SendEmailTest.
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SendEmail::class)]
class SendEmailTest extends TestCase
{
    private SendEmail $sendEmail;

    private MailerInterface&MockObject $mailer;

    private string $senderName;

    private string $senderAddress;

    private string $forbiddenWords;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mailer = $this->createMock(MailerInterface::class);

        $this->sendEmail = new SendEmail(
            $this->mailer,
            $this->senderName = 'foo',
            $this->senderAddress = 'foo@bar',
            $this->forbiddenWords = 'foo,bar',
            ['alias' => 'real@example.com'],
            1,
            10,
            ['text/plain'],
        );
    }

    private function createContact(string $message = 'hello world', iterable $attachments = []): Contact
    {
        return new Contact(
            fromName: "John\r\nDoe",
            fromEmail: 'john@example.com',
            subject: "Subject\nLine",
            message: $message,
            attachments: $attachments,
        );
    }

    public function testInvoke(): void
    {
        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with(
                $this->callback(
                    fn (Email $email): bool => 'real@example.com' === $email->getTo()[0]->getAddress()
                )
            );

        $this->assertInstanceOf(
            SendEmail::class,
            ($this->sendEmail)(
                $this->createStub(ManagerInterface::class),
                $this->createContact(),
                'alias',
            )
        );
    }

    public function testInvokeWithEmptyReceiver(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager
            ->expects($this->once())
            ->method('error')
            ->with($this->isInstanceOf(InvalidArgumentException::class))
            ->willReturnSelf();

        $this->mailer
            ->expects($this->never())
            ->method('send');

        $this->assertInstanceOf(
            SendEmail::class,
            ($this->sendEmail)(
                $manager,
                $this->createContact(),
                '',
            )
        );
    }

    public function testInvokeWithForbiddenWord(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager
            ->expects($this->once())
            ->method('error')
            ->with($this->isInstanceOf(BotForbidden::class))
            ->willReturnSelf();

        $this->mailer
            ->expects($this->never())
            ->method('send');

        $this->assertInstanceOf(
            SendEmail::class,
            ($this->sendEmail)(
                $manager,
                $this->createContact('hello bar world'),
                'contact@example.com',
            )
        );
    }

    public function testInvokeWithInvalidFromEmail(): void
    {
        $this->mailer
            ->expects($this->never())
            ->method('send');

        $contact = $this->createContact();
        $contact->fromEmail = 'not an email';

        $this->expectException(InvalidArgumentException::class);
        ($this->sendEmail)(
            $this->createStub(ManagerInterface::class),
            $contact,
            'contact@example.com',
        );
    }

    public function testInvokeWithTooManyAttachments(): void
    {
        $this->mailer
            ->expects($this->never())
            ->method('send');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('teknoo.space.error.contact.too_many_attachments');
        ($this->sendEmail)(
            $this->createStub(ManagerInterface::class),
            $this->createContact(
                attachments: [
                    new ContactAttachment('a.txt', 'text/plain', 5, 'hello'),
                    new ContactAttachment('b.txt', 'text/plain', 5, 'world'),
                ],
            ),
            'contact@example.com',
        );
    }

    public function testInvokeWithTooLargeAttachment(): void
    {
        $this->mailer
            ->expects($this->never())
            ->method('send');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('teknoo.space.error.contact.file_too_large');
        ($this->sendEmail)(
            $this->createStub(ManagerInterface::class),
            $this->createContact(
                attachments: [
                    new ContactAttachment('a.txt', 'text/plain', 500, 'hello'),
                ],
            ),
            'contact@example.com',
        );
    }

    public function testInvokeWithInvalidAttachmentType(): void
    {
        $this->mailer
            ->expects($this->never())
            ->method('send');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('teknoo.space.error.contact.invalid_file_type');
        ($this->sendEmail)(
            $this->createStub(ManagerInterface::class),
            $this->createContact(
                attachments: [
                    new ContactAttachment('a.exe', 'application/octet-stream', 5, 'hello'),
                ],
            ),
            'contact@example.com',
        );
    }

    public function testInvokeWithValidAttachment(): void
    {
        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with(
                $this->callback(
                    fn (Email $email): bool => 1 === count($email->getAttachments())
                )
            );

        $this->assertInstanceOf(
            SendEmail::class,
            ($this->sendEmail)(
                $this->createStub(ManagerInterface::class),
                $this->createContact(
                    attachments: [
                        new ContactAttachment('a.txt', 'text/plain', 5, 'hello'),
                    ],
                ),
                'contact@example.com',
            )
        );
    }
}
