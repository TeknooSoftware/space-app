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

namespace Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Email;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Contracts\Recipe\Step\Contact\SendEmailInterface;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Email\Exception\BotForbidden;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Email\Exception\InvalidArgumentException;
use Teknoo\Space\Object\DTO\Contact;
use Throwable;

use function count;
use function explode;
use function filter_var;
use function preg_replace;
use function str_contains;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SendEmail implements SendEmailInterface
{
    /**
     * @var string[]
     */
    private array $forbiddenWords = [];

    /**
     * @param array<string, string> $addresses
     */
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $senderName,
        private readonly string $senderAddress,
        string $forbiddenWords = '',
        private readonly array $addresses = [],
        private readonly int $mailMaxAttachments = 5,
        private readonly int $mailMaxFileSize = 204800,
        private readonly array $mailAllowedMimesTypes = ['text/plain', 'image/jpeg', 'image/png', 'image/gif'],
    ) {
        $this->forbiddenWords = explode(',', $forbiddenWords);
    }

    private function detectBots(string $body): void
    {
        foreach ($this->forbiddenWords as &$words) {
            if (!empty($words) && true === str_contains($body, $words)) {
                throw new BotForbidden(
                    message: 'teknoo.space.error.contact.bot_forbidden',
                    code: 403,
                );
            }
        }
    }

    private function createEmail(string $receiverAddress, Contact $contact): Email
    {
        if (!filter_var($contact->fromEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('teknoo.space.error.contact.invalid_email');
        }

        $contact->fromName = (string) preg_replace('/[\r\n]+/', '', $contact->fromName);
        $contact->subject = (string) preg_replace('/[\r\n]+/', '', $contact->subject);

        $email = new Email();
        $email->subject($contact->subject);
        $email->from(new Address($this->senderAddress, $this->senderName));
        $email->to($receiverAddress);
        $email->replyTo(new Address($contact->fromEmail, $contact->fromName));

        $email->text($contact->message);

        if ($this->mailMaxAttachments < count($contact->attachments)) {
            throw new InvalidArgumentException('teknoo.space.error.contact.too_many_attachments');
        }

        foreach ($contact->attachments as $attachment) {
            if ($this->mailMaxFileSize < $attachment->fileLength) {
                throw new InvalidArgumentException('teknoo.space.error.contact.file_too_large');
            }

            // Check file type
            if (!in_array($attachment->mimeType, $this->mailAllowedMimesTypes)) {
                throw new InvalidArgumentException('teknoo.space.error.contact.invalid_file_type');
            }

            $email->attach(
                body: $attachment->fileContent,
                name: $attachment->fileName,
                contentType: $attachment->mimeType,
            );
        }

        return $email;
    }

    public function __invoke(
        ManagerInterface $manager,
        Contact $contact,
        string $receiverAddress,
    ): SendEmailInterface {
        if (empty($receiverAddress)) {
            $manager->error(
                new InvalidArgumentException(
                    code: 500,
                    message: 'teknoo.space.error.contact.missing.receiver'
                )
            );

            return $this;
        }

        if (isset($this->addresses[$receiverAddress])) {
            $receiverAddress = $this->addresses[$receiverAddress];
        }

        try {
            $this->detectBots($contact->message);
        } catch (Throwable $error) {
            $manager->error($error);

            return $this;
        }

        $email = $this->createEmail($receiverAddress, $contact);

        $this->mailer->send($email);

        return $this;
    }
}
