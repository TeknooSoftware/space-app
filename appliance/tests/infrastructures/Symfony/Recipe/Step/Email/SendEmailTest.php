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
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Mailer\MailerInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Space\Infrastructures\Symfony\Recipe\Step\Email\SendEmail;
use Teknoo\Space\Object\DTO\Contact;

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
        );
    }

    public function testInvoke(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('withHeader')
            ->willReturnSelf();

        $this->assertInstanceOf(
            SendEmail::class,
            ($this->sendEmail)(
                $this->createMock(ManagerInterface::class),
                $this->createMock(Contact::class),
                'foo@bar',
            )
        );
    }
}
