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

use function explode;
use function str_contains;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
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
        private MailerInterface $mailer,
        private string $senderName,
        private string $senderAddress,
        string $forbiddenWords = '',
        private array $addresses = [],
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
        $email = new Email();
        $email->subject($contact->subject);
        $email->from(new Address($this->senderAddress, $this->senderName));
        $email->to($receiverAddress);
        $email->replyTo(new Address($contact->fromEmail, $contact->fromName));

        $email->text($contact->message);

        foreach ($contact->attachments as $attachment) {
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
